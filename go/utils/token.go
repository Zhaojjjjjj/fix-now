package utils

import (
	"os"
	"time"

	"github.com/golang-jwt/jwt/v5"
)

func GenerateToken(userId uint) (string, error) {
	claims := jwt.MapClaims{}
	claims["authorized"] = true
	claims["user_id"] = userId
	claims["exp"] = time.Now().Add(time.Hour * 24).Unix() // Token valid for 24 hours

	token := jwt.NewWithClaims(jwt.SigningMethodHS256, claims)
	secret := os.Getenv("API_SECRET")
	if secret == "" {
		secret = "your_secret_string" // Default secret
	}
	return token.SignedString([]byte(secret))
}
