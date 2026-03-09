# Project Management System API (Laravel 12)

Backend implementation for the Backend Developer Skill Test (Set 1).

## Implemented Features

- Sanctum authentication: register, login, logout, me
- Role-based access control (`admin`, `manager`, `user`)
- Project management APIs
- Task management APIs
- Comment APIs
- Custom middleware for API request logging
- Reusable trait for query scopes (`filterByStatus`, `searchByTitle`)
- Service class for task assignment validation
- Queued email notification when task assignment changes
- Caching for project listing endpoint
- Database factories and seeders
- Feature and unit tests

## Requirements

- PHP 8.2+
- Composer
- MySQL 8+ (configured as default)

## Setup

1. Install dependencies:

```bash
composer install
```

2. Copy environment file:

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

3. Generate app key:

```bash
php artisan key:generate
```

4. Run migrations and seed data:

```bash
php artisan migrate:fresh --seed
```

MySQL database (default in `.env.example`):
- Host: `127.0.0.1`
- Port: `3306`
- Database: `project_management_system`
- Username: `root`

SMTP email (default in `.env.example`):
- Mailer: `smtp`
- Host: `smtp.gmail.com`
- Port: `587`
- Scheme: `tls`

5. Start server:

```bash
php artisan serve
```

## Seeded Data

`php artisan migrate:fresh --seed` creates:

- 3 admins
- 3 managers
- 5 users
- 5 projects
- 10 tasks
- 10 comments

Default factory password: `password`

Default seeded login accounts (all use password `password`):
- Admin: `admin1@pms.test`, `admin2@pms.test`, `admin3@pms.test`
- Manager: `manager1@pms.test`, `manager2@pms.test`, `manager3@pms.test`
- User: `user1@pms.test`, `user2@pms.test`, `user3@pms.test`, `user4@pms.test`, `user5@pms.test`

## Authentication

Use Bearer token from `/api/register` or `/api/login`:

```http
Authorization: Bearer {access_token}
```

## API Endpoints

### Auth

- `POST /api/register`
- `POST /api/login`
- `POST /api/logout`
- `GET /api/me`

### Projects

- `GET /api/projects`
- `GET /api/projects/{id}`
- `POST /api/projects` (admin only)
- `PUT /api/projects/{id}` (admin only)
- `DELETE /api/projects/{id}` (admin only)

### Tasks

- `GET /api/projects/{project_id}/tasks`
- `GET /api/tasks/{id}`
- `POST /api/projects/{project_id}/tasks` (manager only)
- `PUT /api/tasks/{id}` (manager or assigned user)
- `DELETE /api/tasks/{id}` (manager only)

### Comments

- `POST /api/tasks/{task_id}/comments`
- `GET /api/tasks/{task_id}/comments`

## Testing

Run all tests:

```bash
php artisan test
```

## Queue and Mail

Task assignment notifications are dispatched via a queue job (`SendTaskAssignedNotificationJob`) and sent as mail notifications (`TaskAssignedNotification`).

For local async processing, run:

```bash
php artisan queue:work
```

## Postman Collection

Import:

- `docs/postman/Project-Management-System.postman_collection.json`

## API Documentation

Detailed endpoint documentation:

- `docs/API_DOCUMENTATION.md`
