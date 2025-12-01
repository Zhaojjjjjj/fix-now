package models

import (
	"time"
)

type BaseModel struct {
	ID        uint      `gorm:"primaryKey" json:"id"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
	Status    int8      `gorm:"default:1" json:"status"`
}

type User struct {
	BaseModel
	RoleID     int    `json:"role_id"`
	Username   string `gorm:"size:30" json:"username"`
	Pwd        string `gorm:"size:50" json:"-"` // Hide password in JSON
	WxmpOpenid string `gorm:"size:50" json:"wxmp_openid"`
	Nickname   string `gorm:"size:50" json:"nickname"`
	ImgAvatar  string `gorm:"size:200" json:"img_avatar"`
	Mobile     string `gorm:"size:30" json:"mobile"`
}

type Project struct {
	BaseModel
	ImgCover    string `gorm:"size:100" json:"img_cover"`
	Description string `gorm:"size:1000" json:"description"`
	Name        string `gorm:"size:100" json:"name"`
}

type Module struct {
	BaseModel
	Sort      int    `json:"sort"`
	ProjectID uint   `json:"project_id"`
	ParentID  uint   `json:"parent_id"`
	Name      string `gorm:"size:20" json:"name"`
}

type UserProject struct {
	BaseModel
	UserID            uint `json:"user_id"`
	ProjectID         uint `json:"project_id"`
	UserProjectStatus int8 `json:"user_project_status"`
}

type Issue struct {
	BaseModel
	UserID    uint   `json:"user_id"`
	CurUserID uint   `json:"cur_user_id"`
	ProjectID uint   `json:"project_id"`
	ModuleID  uint   `json:"module_id"`
	Sn        int    `json:"sn"`
	Type      int8   `json:"type"`
	Priority  int8   `json:"priority"`
	Title     string `gorm:"size:255" json:"title"`
	Content   string `gorm:"type:text" json:"content"`
}

type IssueLog struct {
	BaseModel
	IssueID    uint   `json:"issue_id"`
	UserID     uint   `json:"user_id"`
	NextUserID uint   `json:"next_user_id"`
	Type       int8   `json:"type"`
	Content    string `gorm:"type:text" json:"content"`
}

func (User) TableName() string {
	return "user"
}

func (Project) TableName() string {
	return "project"
}

func (Module) TableName() string {
	return "module"
}

func (UserProject) TableName() string {
	return "user_project"
}

func (Issue) TableName() string {
	return "issue"
}

func (IssueLog) TableName() string {
	return "issue_log"
}
