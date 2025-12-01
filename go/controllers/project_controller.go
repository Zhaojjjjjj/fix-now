package controllers

import (
	"fix-now-backend/config"
	"fix-now-backend/models"
	"net/http"


	"github.com/gin-gonic/gin"
)

func ProjectList(c *gin.Context) {
	userId, _ := c.Get("user_id")
	var user models.User
	config.DB.First(&user, userId)

	var projects []models.Project
	query := config.DB.Model(&models.Project{})

	status := c.Query("status")
	if status != "" {
		query = query.Where("status = ?", status)
	}

	// If regular user (role_id == 2), filter by user_project
	if user.RoleID == 2 {
		query = query.Joins("JOIN user_project ON user_project.project_id = project.id").
			Where("user_project.user_id = ?", user.ID)
	}

	query.Find(&projects)

	// Calculate counts (This is N+1, but simple for now. Optimize with raw SQL if needed)
	type ProjectWithCounts struct {
		models.Project
		IssueCount     int64 `json:"issueCount"`
		UserIssueCount int64 `json:"userIssueCount"`
	}

	var result []ProjectWithCounts
	for _, p := range projects {
		var issueCount int64
		config.DB.Model(&models.Issue{}).Where("project_id = ? AND status <> 8", p.ID).Count(&issueCount)

		var userIssueCount int64
		config.DB.Model(&models.Issue{}).Where("project_id = ? AND cur_user_id = ? AND status <> 8", p.ID, user.ID).Count(&userIssueCount)

		result = append(result, ProjectWithCounts{
			Project:        p,
			IssueCount:     issueCount,
			UserIssueCount: userIssueCount,
		})
	}

	// Also return user info as per original API
	c.JSON(http.StatusOK, gin.H{
		"code": 0,
		"msg":  "success",
		"data": gin.H{
			"projectList": result,
			"user":        user,
		},
	})
}

func ProjectSave(c *gin.Context) {
	var input models.Project
	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	if input.ID > 0 {
		config.DB.Model(&input).Updates(input)
	} else {
		config.DB.Create(&input)
	}

	c.JSON(http.StatusOK, gin.H{"code": 0, "msg": "操作成功"})
}

func ProjectDelete(c *gin.Context) {
	id := c.Query("id")
	config.DB.Delete(&models.Project{}, id)
	c.JSON(http.StatusOK, gin.H{"code": 0, "msg": "操作成功"})
}
