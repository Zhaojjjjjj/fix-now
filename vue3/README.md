# Frontend (Vue 3 + Vite + Unocss)

## Prerequisites

-   Node.js 18+
-   npm or yarn

## Setup

### 1. Install Dependencies

```bash
npm install
```

### 2. Development

```bash
npm run dev
```

The frontend will start on `http://localhost:5173`

### 3. Build for Production

```bash
npm run build
```

The built files will be in `/dist`

## Tech Stack

-   **Framework**: Vue 3 (Composition API + TypeScript)
-   **Build Tool**: Vite
-   **Styling**: Unocss (Atomic CSS)
-   **State Management**: Pinia
-   **Routing**: Vue Router
-   **HTTP Client**: Axios
-   **Icons**: Iconify (Carbon icons)

## Features

-   Modern, clean UI with Unocss
-   JWT authentication
-   Project management
-   Issue tracking
-   Responsive design

## API Proxy

The Vite dev server is configured to proxy `/api` requests to `http://localhost:8080` (backend).
