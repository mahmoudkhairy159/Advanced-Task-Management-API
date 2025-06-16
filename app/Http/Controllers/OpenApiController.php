<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * @OA\Info(
 *     title="Advanced Task Management API",
 *     version="1.0.0",
 *     description="Comprehensive API documentation for the Advanced Task Management System. This API provides endpoints for managing tasks, users, and administrative functions with proper authentication and authorization.",
 *     @OA\Contact(
 *         email="support@taskmanagement.com",
 *         name="API Support Team"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Main API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="jwt",
 *     type="apiKey",
 *     in="header",
 *     name="Authorization",
 *     description="JWT Bearer token authentication. Format: Bearer {token}"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="JWT Bearer token authentication for admin API. Format: Bearer {token}"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Tasks",
 *     description="Task management operations including CRUD operations, status updates, and soft deletes"
 * )
 *
 * @OA\Tag(
 *     name="Users",
 *     description="User management and profile operations"
 * )
 *
 * @OA\Tag(
 *     name="Admin",
 *     description="Administrative operations for system management"
 * )
 *
 * @OA\Tag(
 *     name="Admin Tasks",
 *     description="Admin Task management endpoints"
 * )
 *
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     title="Task",
 *     description="Task model representing a task in the system",
 *     required={"id", "title", "due_date", "assignable_id", "assignable_type", "creator_id", "creator_type", "status", "priority"},
 *     @OA\Property(property="id", type="integer", description="Task ID", example=1),
 *     @OA\Property(property="title", type="string", description="Task title", example="Complete project documentation"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Task description", example="Write comprehensive API documentation using Swagger"),
 *     @OA\Property(property="due_date", type="string", format="date-time", description="Task due date", example="2024-12-31T23:59:59Z"),
 *     @OA\Property(property="priority", type="integer", description="Task priority (0=Low, 1=Medium, 2=High, 3=Critical)", example=2),
 *     @OA\Property(property="status", type="integer", description="Task status (0=Pending, 1=In Progress, 2=Completed, 3=Overdue)", example=0),
 *     @OA\Property(property="assignable_id", type="integer", description="ID of assigned user", example=1),
 *     @OA\Property(property="assignable_type", type="string", description="Type of assignable entity", example="Modules\\User\\App\\Models\\User"),
 *     @OA\Property(property="creator_id", type="integer", description="ID of task creator", example=1),
 *     @OA\Property(property="creator_type", type="string", description="Type of creator entity", example="Modules\\User\\App\\Models\\User"),
 *     @OA\Property(property="updater_id", type="integer", nullable=true, description="ID of last updater", example=1),
 *     @OA\Property(property="updater_type", type="string", nullable=true, description="Type of updater entity", example="Modules\\User\\App\\Models\\User"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp", example="2024-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Last update timestamp", example="2024-01-01T00:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true, description="Soft delete timestamp", example=null)
 * )
 *
 * @OA\Schema(
 *     schema="CreateTaskRequest",
 *     type="object",
 *     title="Create Task Request",
 *     description="Request schema for creating a new task",
 *     required={"title", "due_date", "assignable_id"},
 *     @OA\Property(property="title", type="string", maxLength=255, description="Task title", example="Complete project documentation"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Task description", example="Write comprehensive API documentation using Swagger"),
 *     @OA\Property(property="due_date", type="string", format="date", description="Task due date (must be after today)", example="2024-12-31"),
 *     @OA\Property(property="priority", type="integer", nullable=true, description="Task priority (0=Low, 1=Medium, 2=High, 3=Critical)", example=2),
 *     @OA\Property(property="assignable_id", type="integer", description="ID of user to assign task to", example=1)
 * )
 *
 * @OA\Schema(
 *     schema="UpdateTaskRequest",
 *     type="object",
 *     title="Update Task Request",
 *     description="Request schema for updating an existing task",
 *     @OA\Property(property="title", type="string", maxLength=255, description="Task title", example="Updated task title"),
 *     @OA\Property(property="description", type="string", nullable=true, description="Task description", example="Updated task description"),
 *     @OA\Property(property="due_date", type="string", format="date", description="Task due date", example="2024-12-31"),
 *     @OA\Property(property="priority", type="integer", nullable=true, description="Task priority (0=Low, 1=Medium, 2=High, 3=Critical)", example=1),
 *     @OA\Property(property="assignable_id", type="integer", description="ID of user to assign task to", example=2)
 * )
 *
 * @OA\Schema(
 *     schema="UpdateTaskStatusRequest",
 *     type="object",
 *     title="Update Task Status Request",
 *     description="Request schema for updating task status",
 *     required={"status"},
 *     @OA\Property(property="status", type="integer", description="Task status (0=Pending, 1=In Progress, 2=Completed, 3=Overdue)", example=1)
 * )
 *
 * @OA\Schema(
 *     schema="ApiResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", description="Indicates if the request was successful"),
 *     @OA\Property(property="message", type="string", description="Response message"),
 *     @OA\Property(property="data", type="object", description="Response data"),
 *     @OA\Property(property="errors", type="object", description="Validation errors or error details")
 * )
 *
 * @OA\Schema(
 *     schema="PaginatedResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", description="Indicates if the request was successful"),
 *     @OA\Property(property="data", type="array", @OA\Items(type="object"), description="Array of data items"),
 *     @OA\Property(property="meta", type="object",
 *         @OA\Property(property="current_page", type="integer", description="Current page number"),
 *         @OA\Property(property="last_page", type="integer", description="Last page number"),
 *         @OA\Property(property="per_page", type="integer", description="Items per page"),
 *         @OA\Property(property="total", type="integer", description="Total number of items"),
 *         @OA\Property(property="from", type="integer", description="First item number on current page"),
 *         @OA\Property(property="to", type="integer", description="Last item number on current page")
 *     ),
 *     @OA\Property(property="links", type="object",
 *         @OA\Property(property="first", type="string", description="URL to first page"),
 *         @OA\Property(property="last", type="string", description="URL to last page"),
 *         @OA\Property(property="prev", type="string", description="URL to previous page"),
 *         @OA\Property(property="next", type="string", description="URL to next page")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(property="errors", type="object",
 *         @OA\Property(property="field_name", type="array", @OA\Items(type="string"), example={"The field name is required."})
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UnauthorizedError",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Unauthorized access"),
 *     @OA\Property(property="errors", type="object", example={})
 * )
 *
 * @OA\Schema(
 *     schema="NotFoundError",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Resource not found"),
 *     @OA\Property(property="errors", type="object", example={})
 * )
 *
 * @OA\Schema(
 *     schema="ServerError",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Something went wrong"),
 *     @OA\Property(property="errors", type="object", example={})
 * )
 *
 * @OA\Schema(
 *     schema="ForbiddenError",
 *     type="object",
 *     title="Forbidden Error",
 *     description="Error response when user lacks required permissions",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Insufficient permissions to perform this action"),
 *     @OA\Property(property="data", type="object", nullable=true, example=null)
 * )
 *
 * @OA\Schema(
 *     schema="RateLimitError",
 *     type="object",
 *     title="Rate Limit Error",
 *     description="Error response when rate limit is exceeded",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Too many requests. Please try again later."),
 *     @OA\Property(property="data", type="object", nullable=true, example=null)
 * )
 */
class OpenApiController extends Controller
{
    // This controller is used only for OpenAPI documentation
    // No actual implementation needed
}
