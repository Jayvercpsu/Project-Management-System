# Project Management System API Documentation

## Base URL

`/api`

## Authentication Endpoints

### Register User
- Endpoint: `POST /api/register`
- Description: Registers a new user account with default role `user`
- Authentication: Not required

### Login
- Endpoint: `POST /api/login`
- Description: Authenticates a user and returns Sanctum access token
- Authentication: Not required

### Logout
- Endpoint: `POST /api/logout`
- Description: Revokes current access token
- Authentication: Required

### Get Current User
- Endpoint: `GET /api/me`
- Description: Retrieves currently authenticated user
- Authentication: Required

## Project Management Endpoints

### Retrieve Projects
- Endpoint: `GET /api/projects`
- Description: Returns project list (cached)
- Authentication: Required

### Retrieve Project
- Endpoint: `GET /api/projects/{projectId}`
- Description: Returns project details with tasks
- Authentication: Required

### Create Project
- Endpoint: `POST /api/projects`
- Description: Creates a new project
- Required Role: `admin`

### Update Project
- Endpoint: `PUT /api/projects/{projectId}`
- Description: Updates an existing project
- Required Role: `admin`

### Delete Project
- Endpoint: `DELETE /api/projects/{projectId}`
- Description: Deletes a project
- Required Role: `admin`

## Task Management Endpoints

### Retrieve Tasks By Project
- Endpoint: `GET /api/projects/{projectId}/tasks`
- Description: Returns all tasks under a project
- Authentication: Required

### Retrieve Task
- Endpoint: `GET /api/tasks/{taskId}`
- Description: Returns a specific task
- Authentication: Required

### Create Task
- Endpoint: `POST /api/projects/{projectId}/tasks`
- Description: Creates and assigns a task
- Required Role: `manager`

### Update Task
- Endpoint: `PUT /api/tasks/{taskId}`
- Description: Updates task fields (manager) or status only (assigned user)
- Required Role: `manager` or assigned `user`

### Delete Task
- Endpoint: `DELETE /api/tasks/{taskId}`
- Description: Deletes task
- Required Role: `manager`

## Comment Endpoints

### Add Comment
- Endpoint: `POST /api/tasks/{taskId}/comments`
- Description: Adds a comment to a task
- Authentication: Required

### Get Comments
- Endpoint: `GET /api/tasks/{taskId}/comments`
- Description: Retrieves task comments
- Authentication: Required

## Role Rules

- `admin`: full Project CRUD
- `manager`: Task create/update/delete
- `user`: update assigned task status, add/view comments on assigned tasks

## Middleware

- `auth:sanctum`: route protection
- `role`: role-based authorization middleware
- `LogApiRequest`: logs `user_id`, endpoint, and timestamp on each API request

## Reusable Components

- `CommonQueryScopes` trait:
  - `filterByStatus()`
  - `searchByTitle()`
- `TaskAssignmentService`: central task assignment validation and notification dispatch

## Notifications, Queues, and Caching

- New task assignments trigger queued job: `SendTaskAssignedNotificationJob`
- Job sends email notification: `TaskAssignedNotification`
- Project listing uses cache (`GET /api/projects`)

## Standard Response Format

Success:

```json
{
  "success": true,
  "message": "...",
  "data": {}
}
```

Error:

```json
{
  "success": false,
  "message": "...",
  "errors": {}
}
```
