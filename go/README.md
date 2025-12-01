# Backend (Go + Gin + GORM)

## Prerequisites

-   Go 1.23+
-   MySQL 8+

## Setup

### 1. Database Configuration

Set the following environment variables (or use defaults):

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_USER=root
export DB_PASSWORD=your_password
export DB_NAME=fix_now
export API_SECRET=your_secret_key
```

### 2. Import Database Schema

Import the database schema from `/think/db.sql`:

```bash
mysql -u root -p fix_now < ../think/db.sql
```

### 3. Install Dependencies

```bash
go mod tidy
```

### 4. Run Backend

```bash
go run main.go
```

The server will start on `http://localhost:8080`

## API Endpoints

### Public

-   `POST /login/login` - Login

### Protected (Requires JWT token)

-   `GET /user/edit` - Get current user
-   `GET /project/list` - List projects
-   `POST /project/edit` - Create/Update project
-   `POST /project/del` - Delete project
-   `GET /module/list` - List modules
-   `POST /module/updateAll` - Update modules
-   `POST /module/del` - Delete modules
-   `GET /issue/list` - List issues
-   `GET /issue/edit` - Get issue details
-   `POST /issue/edit` - Create/Update issue
-   `GET /issue/logList` - Get issue logs
-   `POST /issue/deal` - Handle issue workflow
