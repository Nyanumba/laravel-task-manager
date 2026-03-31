# TaskManager — Laravel Task Management System

A modern **Task Management System** built with **Laravel 10**, featuring a beautiful AdminLTE 3 UI, intelligent status workflow, and a fully documented RESTful API.

## Live Demo

🌐 **[https://laravel-task-manager-production-98a6.up.railway.app](https://laravel-task-manager-production-98a6.up.railway.app)**

---

## Features

- Clean, responsive interface using **AdminLTE 3** + Bootstrap 4
- Task status workflow: **Pending → In Progress → Done** (strict forward-only)
- Sidebar and toolbar filtering: **All | Pending | In Progress | Done**
- Priority system: **Low | Medium | High** (sorted high→low in all views)
- Due date validation — today or future dates only
- Business rules enforced:
  - Cannot skip or revert status steps
  - Only `done` tasks can be deleted (403 Forbidden otherwise)
  - No duplicate task title on the same due date
- Toast notifications and SweetAlert2 confirmations
- Full **RESTful API** with proper JSON responses
- Built-in **API Documentation** page with live cURL examples
- **Daily Report** with Chart.js doughnut chart
- Deployed on **Railway** with **MySQL**

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 10, PHP 8.2+ |
| Frontend | Blade Templates, AdminLTE 3, Bootstrap 4 |
| JavaScript | jQuery, Vanilla JS, SweetAlert2, Chart.js |
| Database | MySQL 8.x |
| Deployment | Railway (Docker) |

---

## Project Structure
```
task-manager/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── TaskController.php       ← API endpoints
│   │   │   └── TaskViewController.php   ← Web UI pages
│   │   └── Requests/
│   │       ├── CreateTaskRequest.php
│   │       └── UpdateTaskStatusRequest.php
│   ├── Models/
│   │   └── Task.php                     ← Eloquent model + business logic
│   └── Services/
│       └── TaskService.php              ← Business logic layer (OOP)
├── database/
│   ├── migrations/
│   │   └── xxxx_create_tasks_table.php
│   ├── seeders/
│   │   └── TaskSeeder.php
│   └── task_manager_dump.sql            ← Ready-to-import MySQL dump
├── resources/views/
│   ├── layouts/app.blade.php            ← AdminLTE master layout
│   └── tasks/
│       ├── index.blade.php              ← Task list + create modal
│       ├── report.blade.php             ← Daily report + chart
│       └── api-docs.blade.php           ← API reference page
├── routes/
│   ├── api.php                          ← REST API routes
│   └── web.php                          ← UI routes
├── tests/
│   ├── Feature/TaskApiTest.php          ← 25 feature tests
│   └── Unit/TaskServiceTest.php         ← 20 unit tests
├── Dockerfile
├── start.sh
└── railway.json
```

---

## How to Run Locally

### Prerequisites

- PHP 8.2+
- Composer
- MySQL 8.x
- Git

### Step 1 — Clone the Repository
```bash
git clone https://github.com/Nyanumba/laravel-task-manager.git
cd laravel-task-manager
```

### Step 2 — Install PHP Dependencies
```bash
composer install
```

### Step 3 — Set Up Environment File
```bash
cp .env.example .env
php artisan key:generate
```

### Step 4 — Configure the Database

Open `.env` and update the database section:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

### Step 5 — Create the MySQL Database
```bash
mysql -u root -p -e "CREATE DATABASE task_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 6 — Run Migrations and Seed Data
```bash
# Run migrations only
php artisan migrate

# Run migrations and seed sample data
php artisan migrate --seed
```

Alternatively, import the SQL dump directly:
```bash
mysql -u root -p task_manager < database/task_manager_dump.sql
```

### Step 7 — Start the Development Server
```bash
php artisan serve
```

Visit **http://localhost:8000** — the AdminLTE dashboard loads automatically.

### Available Pages

| URL | Description |
|---|---|
| `http://localhost:8000/tasks` | Main task dashboard |
| `http://localhost:8000/tasks?status=pending` | Pending tasks only |
| `http://localhost:8000/tasks?status=in_progress` | In progress tasks only |
| `http://localhost:8000/tasks?status=done` | Done tasks only |
| `http://localhost:8000/tasks/report` | Daily report with chart |
| `http://localhost:8000/api-docs` | API reference documentation |
| `http://localhost:8000/api/tasks` | Raw API endpoint |

---

## Running Tests

Tests use a separate MySQL database so your real data is never touched.

### Step 1 — Create the Test Database
```bash
mysql -u root -p -e "CREATE DATABASE task_manager_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Step 2 — Update phpunit.xml

Make sure `phpunit.xml` has these values:
```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_DATABASE" value="task_manager_testing"/>
<env name="DB_USERNAME" value="root"/>
<env name="DB_PASSWORD" value="your_password"/>
```

### Step 3 — Run the Tests
```bash
# Run all tests
php artisan test

# Run only feature tests
php artisan test --testsuite=Feature

# Run only unit tests
php artisan test --testsuite=Unit

# Run a single test
php artisan test --filter test_can_create_a_task
```

Expected output:
```
PASS  Tests\Unit\TaskServiceTest     20 tests
PASS  Tests\Feature\TaskApiTest      25 tests

Tests:  45 passed
```

---

## How to Deploy on Railway

### Prerequisites

- A [Railway](https://railway.app) account linked to GitHub
- Your project pushed to a GitHub repository

### Step 1 — Create a New Railway Project

1. Go to [railway.app](https://railway.app) → **New Project**
2. Select **Deploy from GitHub repo**
3. Select your `laravel-task-manager` repository

### Step 2 — Add a MySQL Database

1. In your project dashboard click **+ New**
2. Select **Database** → **MySQL**
3. Railway provisions MySQL automatically

### Step 3 — Set Environment Variables

Click your **web service** → **Variables** tab → add each variable:
```
APP_NAME=TaskFlow
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.up.railway.app
APP_KEY=base64:your_generated_key_here

DB_CONNECTION=mysql
DB_HOST=       ← from MySQL service Connect tab
DB_PORT=       ← from MySQL service Connect tab
DB_DATABASE=   ← from MySQL service Connect tab
DB_USERNAME=   ← from MySQL service Connect tab
DB_PASSWORD=   ← from MySQL service Connect tab

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
LOG_CHANNEL=stderr
LOG_LEVEL=error
```

Generate `APP_KEY` locally:
```bash
php artisan key:generate --show
```

Get MySQL values from Railway → MySQL service → **Connect** tab.

### Step 4 — Deploy

Railway deploys automatically on every push to your main branch.

To trigger a manual deploy:
```bash
git add .
git commit -m "deploy to railway"
git push origin main
```

### Step 5 — Verify Deployment

Watch **Deploy Logs** — a successful deployment shows:
```
=== TaskFlow Starting ===
PORT=8080
APP_ENV=production
INFO  Nothing to migrate.
=== Starting server on port 8080 ===
Starting Laravel development server: http://0.0.0.0:8080
```

---

## How to Deploy on Render

### Step 1 — Create a Web Service

1. Go to [render.com](https://render.com) → **New** → **Web Service**
2. Connect your GitHub repository
3. Set **Runtime** to **Docker**

### Step 2 — Add Environment Variables

Same variables as Railway above — add them in Render's **Environment** tab.

### Step 3 — Add a MySQL Database

1. Go to Render → **New** → **PostgreSQL** (Render's free DB) or use [PlanetScale](https://planetscale.com) for free MySQL
2. Copy the connection string and set the individual `DB_*` variables

### Step 4 — Deploy

Render deploys automatically on push. Your app will be live at `https://your-app.onrender.com`.

---

## API Reference

Base URL (local): `http://localhost:8000`  
Base URL (production): `https://laravel-task-manager-production-98a6.up.railway.app`

All endpoints return JSON. Send `Accept: application/json` header with every request.

---

### 1. Create Task
```
POST /api/tasks
```

**Request body:**
```json
{
  "title": "Deploy to production",
  "due_date": "2026-05-01",
  "priority": "high"
}
```

**Rules:**
- `title` must be unique per `due_date`
- `due_date` must be today or a future date
- `priority` must be `low`, `medium`, or `high`
- `status` is always set to `pending` automatically

**Success response `201`:**
```json
{
  "message": "Task created successfully.",
  "data": {
    "id": 1,
    "title": "Deploy to production",
    "due_date": "2026-05-01",
    "priority": "high",
    "status": "pending",
    "created_at": "2026-04-01T10:00:00.000000Z",
    "updated_at": "2026-04-01T10:00:00.000000Z"
  }
}
```

**Validation error `422`:**
```json
{
  "message": "The title has already been taken.",
  "errors": {
    "title": ["A task with this title already exists for that due date."]
  }
}
```

---

### 2. List Tasks
```
GET /api/tasks
GET /api/tasks?status=pending
GET /api/tasks?status=in_progress
GET /api/tasks?status=done
```

**Rules:**
- Sorted by priority (high → medium → low) then `due_date` ascending
- Optional `?status=` filter

**Success response `200`:**
```json
{
  "message": "Found 3 task(s).",
  "data": [
    {
      "id": 1,
      "title": "Deploy to production",
      "due_date": "2026-05-01",
      "priority": "high",
      "status": "pending"
    }
  ]
}
```

**When no tasks exist:**
```json
{
  "message": "No tasks found.",
  "data": []
}
```

---

### 3. Update Task Status
```
PATCH /api/tasks/{id}/status
```

**Request body:**
```json
{
  "status": "in_progress"
}
```

**Allowed transitions only:**
```
pending → in_progress → done
```

**Success response `200`:**
```json
{
  "message": "Task status updated successfully.",
  "data": {
    "id": 1,
    "status": "in_progress"
  }
}
```

**Illegal transition `422`:**
```json
{
  "message": "Cannot change status from 'pending' to 'done'. Only allowed next: 'in_progress'."
}
```

---

### 4. Delete Task
```
DELETE /api/tasks/{id}
```

**Rules:** Only `done` tasks can be deleted.

**Success response `200`:**
```json
{
  "message": "Task 'Deploy to production' deleted successfully."
}
```

**Not done `403`:**
```json
{
  "message": "Task cannot be deleted because status is 'pending'. Only 'done' tasks may be deleted."
}
```

---

### 5. Daily Report
```
GET /api/tasks/report?date=YYYY-MM-DD
```

**Success response `200`:**
```json
{
  "date": "2026-04-01",
  "summary": {
    "high": {
      "pending": 2,
      "in_progress": 1,
      "done": 0
    },
    "medium": {
      "pending": 1,
      "in_progress": 0,
      "done": 3
    },
    "low": {
      "pending": 0,
      "in_progress": 0,
      "done": 1
    }
  }
}
```

---

## Example cURL Requests

Replace `BASE` with your local or production URL.
```bash
# Set base URL
BASE=https://laravel-task-manager-production-98a6.up.railway.app

# --- CREATE ---
curl -X POST $BASE/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Fix login bug","due_date":"2026-05-10","priority":"high"}'

# --- LIST ALL ---
curl $BASE/api/tasks

# --- LIST BY STATUS ---
curl "$BASE/api/tasks?status=pending"
curl "$BASE/api/tasks?status=in_progress"
curl "$BASE/api/tasks?status=done"

# --- ADVANCE STATUS (pending → in_progress) ---
curl -X PATCH $BASE/api/tasks/1/status \
  -H "Content-Type: application/json" \
  -d '{"status":"in_progress"}'

# --- ADVANCE STATUS (in_progress → done) ---
curl -X PATCH $BASE/api/tasks/1/status \
  -H "Content-Type: application/json" \
  -d '{"status":"done"}'

# --- DELETE (only works on done tasks) ---
curl -X DELETE $BASE/api/tasks/1

# --- DAILY REPORT ---
curl "$BASE/api/tasks/report?date=2026-04-01"
```

---

## Database

**Engine:** MySQL 8.x  
**Database:** `task_manager`

### Schema: tasks table

| Column | Type | Details |
|---|---|---|
| `id` | BIGINT UNSIGNED | Primary key, auto increment |
| `title` | VARCHAR(255) | Required |
| `due_date` | DATE | Required, today or future |
| `priority` | ENUM | `low`, `medium`, `high` |
| `status` | ENUM | `pending`, `in_progress`, `done` |
| `created_at` | TIMESTAMP | Laravel default |
| `updated_at` | TIMESTAMP | Laravel default |

**Unique constraint:** `(title, due_date)` — enforced at both application and database level.

---

## Business Rules Summary

| Rule | Detail