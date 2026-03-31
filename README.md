# TaskManager

A modern and elegant **Task Management System** built with **Laravel 13**, featuring a beautiful AdminLTE UI, intelligent status workflow, and a fully documented RESTful API.

![TaskManager Banner](https://via.placeholder.com/1200x400/1a1a2e/ffffff?text=TaskFlow+-+Task+Management+System)

## ✨ Features

- Clean, responsive interface using **AdminLTE 3** + Bootstrap 4
- Task status workflow: **Pending → In Progress → Done**
- Advanced filtering with tabs: **All | Pending | In Progress | Done**
- Priority system: Low, Medium, High
- Due date validation (today or future only)
- Smart business rules (cannot skip status steps, only "Done" tasks can be deleted)
- Toast notifications and SweetAlert2 confirmations
- Full **RESTful API** with proper JSON responses
- Built-in **API Documentation** page with cURL examples
- SQLite by default (easy setup), MySQL supported

## 🛠 Tech Stack

- **Backend**: Laravel 13.x (PHP 8.5+)
- **Frontend**: Blade Templates, AdminLTE 3, Bootstrap 4, Font Awesome 6
- **JavaScript**: jQuery + Vanilla JS
- **Database**: SQLite (default) / MySQL
- **Styling**: Custom modern CSS with cards and badges

## 🚀 Quick Start

### 1. Clone the Repository
# 
git clone https://github.com/Nyanumba/laravel-task-manager.git
cd task-manager
# 2 Install Dependencies
Composer install

# 3 Setup Environment
cp .env.example .env
php artisan key:generate
# 4 Database Setup
php artisan migrate
# 5 Run the Application
php artisan serve
Visit: http://localhost:8000

# 📋 Usage Guide
Web Interface (/tasks)

Click "New Task" to create tasks via modal
Use the top tabs to filter tasks by status
Click "→ Next Status" button to advance task status
Delete tasks only when they are in Done status
Real-time stats cards show counts per status

Sidebar Navigation

All Tasks, Pending, In Progress, Done
Daily Report
API Documentation

API Endpoints
Full interactive documentation is available at:
http://localhost:8000/api-docs
