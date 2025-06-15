<?php

use App\Controller\EmployeeController;
use App\Middleware\CorsMiddleware;
use Slim\App;

return function (App $app) {
    // Add CORS middleware
    $app->add(CorsMiddleware::class);
    
    // API routes
    $app->group('/api/v1', function ($group) {
        // Employee CRUD routes
        $group->get('/employees', [EmployeeController::class, 'index']);
        $group->get('/employees/{id}', [EmployeeController::class, 'show']);
        $group->post('/employees', [EmployeeController::class, 'store']);
        $group->put('/employees/{id}', [EmployeeController::class, 'update']);
        $group->delete('/employees/{id}', [EmployeeController::class, 'destroy']);
        
        // Additional utility routes
        $group->get('/departments', [EmployeeController::class, 'getDepartments']);
        $group->get('/statistics', [EmployeeController::class, 'getStatistics']);
    });

    // Health check
    $app->get('/health', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'status' => 'OK', 
            'timestamp' => date('c'),
            'version' => '1.0.0',
            'environment' => $_ENV['APP_ENV'] ?? 'development'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // API Info
    $app->get('/api', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'name' => 'Employee Management API',
            'version' => '1.0.0',
            'endpoints' => [
                'GET /api/v1/employees' => 'Get all employees (with pagination & filters)',
                'GET /api/v1/employees/{id}' => 'Get employee by ID',
                'POST /api/v1/employees' => 'Create new employee',
                'PUT /api/v1/employees/{id}' => 'Update employee',
                'DELETE /api/v1/employees/{id}' => 'Delete employee',
                'GET /api/v1/departments' => 'Get all departments',
                'GET /api/v1/statistics' => 'Get employee statistics',
                'GET /health' => 'Health check'
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};