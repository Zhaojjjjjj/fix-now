package controllers

import (
	"fix-now-backend/config"
	"fix-now-backend/models"
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"
)

func IssueList(c *gin.Context) {
	userId, _ := c.Get("user_id")
	var user models.User
	config.DB.First(&user, userId)

	var issues []models.Issue
	query := config.DB.Model(&models.Issue{})

	projectId := c.Query("project_id")
	if projectId != "" {
		query = query.Where("project_id = ?", projectId)
	}

	priority := c.Query("priority")
	if priority != "" {
		query = query.Where("priority = ?", priority)
	}

	status := c.Query("status")
	if status != "" {
		query = query.Where("status = ?", status)
	}

	title := c.Query("title")
	if title != "" {
		query = query.Where("title LIKE ?", "%"+title+"%")
	}

	searchIssue := c.Query("searchIssue")
	if searchIssue == "1" {
		query = query.Where("cur_user_id = ? AND status <> 8", user.ID)
	} else if searchIssue == "2" {
		query = query.Where("status <> 8")
	}

	if user.RoleID == 2 {
		query = query.Where("user_id = ?", user.ID)
	}

	// Pagination
	page, _ := strconv.Atoi(c.DefaultQuery("page", "1"))
	limit, _ := strconv.Atoi(c.DefaultQuery("limit", "10"))
	offset := (page - 1) * limit

	var total int64
	query.Count(&total)

	query.Order("status asc, priority desc, id desc").Limit(limit).Offset(offset).Find(&issues)

	// Enrich data (User, CurUser, Module) - N+1 again, but okay for now
	type IssueWithDetails struct {
		models.Issue
		User    models.User   `json:"user"`
		CurUser models.User   `json:"curUser"`
		Module  models.Module `json:"module"`
	}

	var result []IssueWithDetails
	for _, issue := range issues {
		var u models.User
		config.DB.First(&u, issue.UserID)
		var cu models.User
		config.DB.First(&cu, issue.CurUserID)
		var m models.Module
		config.DB.First(&m, issue.ModuleID)

		result = append(result, IssueWithDetails{
			Issue:   issue,
			User:    u,
			CurUser: cu,
			Module:  m,
		})
	}

	c.JSON(http.StatusOK, gin.H{
		"code": 0,
		"msg":  "success",
		"data": gin.H{
			"total": total,
			"data":  result,
		},
	})
}

func IssueEditInfo(c *gin.Context) {
	id := c.Query("id")
	projectId := c.Query("project_id")

	var issue models.Issue
	if id != "" {
		config.DB.First(&issue, id)
	}

	var project models.Project
	if issue.ID > 0 {
		config.DB.First(&project, issue.ProjectID)
	} else if projectId != "" {
		config.DB.First(&project, projectId)
	}

	var users []models.User
	config.DB.Where("status = 1").Find(&users)

	var modules []models.Module
	config.DB.Where("status = 1").Find(&modules)
	moduleTree := buildTree(modules, 0)

	c.JSON(http.StatusOK, gin.H{
		"code": 0,
		"msg":  "success",
		"data": gin.H{
			"id":         issue.ID,
			"title":      issue.Title,
			"content":    issue.Content,
			"priority":   issue.Priority,
			"type":       issue.Type,
			"module_id":  issue.ModuleID,
			"project_id": issue.ProjectID,
			"project":    project,
			"userList":   users,
			"moduleList": moduleTree,
		},
	})
}

func IssueSave(c *gin.Context) {
	userId, _ := c.Get("user_id")
	var input models.Issue
	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	input.UserID = userId.(uint)
	input.Status = 1 // STATUS_NOTSOLVE

	if input.ID > 0 {
		config.DB.Model(&input).Updates(input)
	} else {
		config.DB.Create(&input)
	}

	// Create Log
	log := models.IssueLog{
		IssueID: input.ID,
		UserID:  userId.(uint),
		Type:    0, // TYPE_ADD
		Content: input.Content,
	}
	config.DB.Create(&log)

	c.JSON(http.StatusOK, gin.H{"code": 0, "msg": "操作成功", "data": input})
}

func IssueLogList(c *gin.Context) {
	id := c.Query("id")
	var logs []models.IssueLog
	config.DB.Where("issue_id = ?", id).Order("created_at asc").Find(&logs)

	// Enrich with User info
	type LogWithUser struct {
		models.IssueLog
		User     models.User `json:"user"`
		NextUser models.User `json:"nextUser"`
	}

	var result []LogWithUser
	for _, l := range logs {
		var u models.User
		config.DB.First(&u, l.UserID)
		var nu models.User
		if l.NextUserID > 0 {
			config.DB.First(&nu, l.NextUserID)
		}
		result = append(result, LogWithUser{
			IssueLog: l,
			User:     u,
			NextUser: nu,
		})
	}

	c.JSON(http.StatusOK, gin.H{"code": 0, "msg": "success", "data": result})
}

type DealInput struct {
	ID        uint   `json:"id"`
	Type      int8   `json:"type"`
	Content   string `json:"content"`
	CurUserID uint   `json:"cur_user_id"`
}

func IssueDeal(c *gin.Context) {
	userId, _ := c.Get("user_id")
	var input DealInput
	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	var issue models.Issue
	if err := config.DB.First(&issue, input.ID).Error; err != nil {
		c.JSON(http.StatusNotFound, gin.H{"error": "Issue not found"})
		return
	}

	var logType int8
	
	switch input.Type {
	case 2: // FIX
		issue.Status = 2 // STATUS_AUDIT
		issue.CurUserID = issue.UserID
		logType = 2
	case 3: // REJECT
		issue.Status = 2 // STATUS_AUDIT
		issue.CurUserID = issue.UserID
		logType = 3
	case 4: // VER_REJECT
		// Find last fix/reject log to get user?
		// Simplified: just set to not solve
		issue.Status = 1 // STATUS_NOTSOLVE
		// issue.CurUserID = ... (Skip complex logic for now)
		logType = 4
	case 5: // COMMENT
		logType = 5
	case 6: // ASSIGN
		issue.Status = 1 // STATUS_NOTSOLVE
		issue.CurUserID = input.CurUserID
		logType = 6
	case 8: // FINISH
		issue.Status = 8 // STATUS_FINISH
		issue.CurUserID = 0
		logType = 8
	}

	config.DB.Save(&issue)

	// Create Log
	log := models.IssueLog{
		IssueID:    issue.ID,
		UserID:     userId.(uint),
		NextUserID: issue.CurUserID,
		Type:       logType,
		Content:    input.Content,
	}
	config.DB.Create(&log)

	c.JSON(http.StatusOK, gin.H{"code": 0, "msg": "操作成功", "data": issue})
}
