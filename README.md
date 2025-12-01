# MyBug - Issue Tracking System

A modern, lightweight issue tracking system rewritten from scratch.

## Architecture

### Backend

-   **Language**: Go
-   **Framework**: Gin
-   **ORM**: GORM
-   **Database**: MySQL
-   **Auth**: JWT

### Frontend

-   **Framework**: Vue 3 + TypeScript
-   **Build Tool**: Vite
-   **Styling**: Unocss
-   **State Management**: Pinia
-   **Routing**: Vue Router

## Quick Start

### 1. Setup Database

```bash
# Create database
mysql -u root -p
CREATE DATABASE fix_now CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

# Import schema
mysql -u root -p fix_now < think/db.sql
```

### 2. Start Backend

```bash
cd backend
export DB_USER=root
export DB_PASSWORD=your_password
export DB_NAME=fix_now
go run main.go
```

Backend runs on http://localhost:8080

### 3. Start Frontend

```bash
cd frontend
npm install
npm run dev
```

Frontend runs on http://localhost:5173

## Project Structure

```
.
├── backend/              # Go backend
│   ├── config/          # Database config
│   ├── models/          # GORM models
│   ├── controllers/     # Request handlers
│   ├── routes/          # API routes
│   ├── middleware/      # JWT middleware
│   └── utils/           # Utilities
│
├── frontend/            # Vue 3 frontend
│   ├── src/
│   │   ├── api/        # Axios setup
│   │   ├── stores/     # Pinia stores
│   │   ├── router/     # Vue Router
│   │   ├── views/      # Page components
│   │   └── components/ # Reusable components
│   └── uno.config.ts   # Unocss config
│
├── think/               # Legacy PHP backend (for reference)
└── vue3/                # Legacy Vue3 frontend (for reference)
```

## Features

-   ✅ User authentication (JWT)
-   ✅ Project management
-   ✅ Module/component organization
-   ✅ Issue tracking
-   ✅ Issue workflow (Open → Audit → Closed)
-   ✅ Issue assignment
-   ✅ Issue logging
-   ✅ Priority levels
-   ✅ Modern, responsive UI

## Migration Notes

This is a complete rewrite of the original ThinkPHP + Vue3 application. The new version:

-   Uses Go instead of PHP for better performance
-   Uses Unocss instead of Bootstrap/Arco Design for smaller bundle size
-   Simplified architecture and cleaner codebase
-   Retains all core functionality

The legacy `think/` and `vue3/` directories are kept for reference and can be removed once migration is complete.
