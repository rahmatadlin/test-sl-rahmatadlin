<?php

use App\Controller\EmployeeController;
use App\Middleware\CorsMiddleware;
use Slim\App;

return function (App $app) {
    // Add CORS middleware
    $app->add(CorsMiddleware::class);
    
    // API routes
    $app->group('/api/v1', function ($group) {
        $group->get('/employees', [EmployeeController::class, 'index']);
        $group->get('/employees/{id}', [EmployeeController::class, 'show']);
        $group->post('/employees', [EmployeeController::class, 'store']);
        $group->put('/employees/{id}', [EmployeeController::class, 'update']);
        $group->delete('/employees/{id}', [EmployeeController::class, 'destroy']);
    });

    // Health check
    $app->get('/health', function ($request, $response) {
        $response->getBody()->write(json_encode(['status' => 'OK', 'timestamp' => date('c')]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};