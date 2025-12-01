package main

import (
	"fix-now-backend/config"
	"fix-now-backend/routes"
)

func main() {
	config.ConnectDatabase()
	r := routes.SetupRouter()
	r.Run(":8080")
}
