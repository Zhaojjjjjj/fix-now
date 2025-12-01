package routes

import (
	"fix-now-backend/controllers"
	"fix-now-backend/middleware"

	"github.com/gin-gonic/gin"
)

func SetupRouter() *gin.Engine {
	r := gin.Default()

	// CORS Middleware
	r.Use(func(c *gin.Context) {
		c.Writer.Header().Set("Access-Control-Allow-Origin", "*")
		c.Writer.Header().Set("Access-Control-Allow-Credentials", "true")
		c.Writer.Header().Set("Access-Control-Allow-Headers", "Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization, accept, origin, Cache-Control, X-Requested-With")
		c.Writer.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS, GET, PUT, DELETE")

		if c.Request.Method == "OPTIONS" {
			c.AbortWithStatus(204)
			return
		}

		c.Next()
	})

	// Public routes
	r.POST("/login/login", controllers.Login)
	// r.GET("/login/logout", controllers.Logout) // Client side just deletes token

	// Protected routes
	api := r.Group("/")
	api.Use(middleware.JwtAuthMiddleware())
	{
		// User
		api.GET("/user/edit", controllers.CurrentUser) // Simplified: just get current user
		// api.POST("/user/edit", controllers.UserUpdate) // Implement if needed
		// api.POST("/user/pwd", controllers.UserPwd)
		// api.GET("/user/list", controllers.UserList)

		// Project
		api.GET("/project/list", controllers.ProjectList)
		api.POST("/project/edit", controllers.ProjectSave)
		// api.GET("/project/edit", controllers.ProjectDetail) // If needed
		api.POST("/project/del", controllers.ProjectDelete)

		// Module
		api.GET("/module/list", controllers.ModuleList)
		api.POST("/module/updateAll", controllers.ModuleUpdateAll)
		api.POST("/module/del", controllers.ModuleDelete)

		// Issue
		api.GET("/issue/list", controllers.IssueList)
		api.GET("/issue/edit", controllers.IssueEditInfo)
		api.POST("/issue/edit", controllers.IssueSave)
		api.GET("/issue/logList", controllers.IssueLogList)
		api.POST("/issue/deal", controllers.IssueDeal)
	}

	return r
}
