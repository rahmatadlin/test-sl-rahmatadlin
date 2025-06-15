<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\EmployeeService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;

class EmployeeController
{
    private EmployeeService $service;
    private LoggerInterface $logger;

    public function __construct(EmployeeService $service, LoggerInterface $logger)
    {
        $this->service = $service;
        $this->logger = $logger;
    }

    public function index(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            
            $page = (int) ($queryParams['page'] ?? 1);
            $limit = (int) ($queryParams['limit'] ?? 50);
            
            $filters = [];
            if (!empty($queryParams['departemen'])) {
                $filters['departemen'] = $queryParams['departemen'];
            }
            if (!empty($queryParams['status'])) {
                $filters['status'] = $queryParams['status'];
            }
            if (!empty($queryParams['jabatan'])) {
                $filters['jabatan'] = $queryParams['jabatan'];
            }
            if (!empty($queryParams['search'])) {
                $filters['search'] = $queryParams['search'];
            }
            if (!empty($queryParams['sort_field'])) {
                $filters['sort_field'] = $queryParams['sort_field'];
            }
            if (!empty($queryParams['sort_order'])) {
                $filters['sort_order'] = $queryParams['sort_order'];
            }

            $result = $this->service->getAllEmployees($page, $limit, $filters);
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Employees retrieved successfully',
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving employees', ['error' => $e->getMessage()]);
            
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to retrieve employees',
                'error' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(500);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $employee = $this->service->getEmployeeById($id);
            
            if (!$employee) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Employee not found'
                ]));
                
                return $response->withHeader('Content-Type', 'application/json')
                              ->withStatus(404);
            }
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Employee retrieved successfully',
                'data' => $employee
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving employee', ['id' => $args['id'], 'error' => $e->getMessage()]);
            
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to retrieve employee',
                'error' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(500);
        }
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            
            if (!$data) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Request body is required'
                ]));
                
                return $response->withHeader('Content-Type', 'application/json')
                              ->withStatus(400);
            }
            
            $employee = $this->service->createEmployee($data);
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Employee created successfully',
                'data' => $employee
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(201);
            
        } catch (InvalidArgumentException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Validation error',
                'error' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(400);
            
        } catch (\Exception $e) {
            $this->logger->error('Error creating employee', ['error' => $e->getMessage()]);
            
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to create employee',
                'error' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(500);
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $data = $request->getParsedBody();
            
            if (!$data) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Request body is required'
                ]));
                
                return $response->withHeader('Content-Type', 'application/json')
                              ->withStatus(400);
            }
            
            $employee = $this->service->updateEmployee($id, $data);
            
            if (!$employee) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Employee not found'
                ]));
                
                return $response->withHeader('Content-Type', 'application/json')
                              ->withStatus(404);
            }
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Employee updated successfully',
                'data' => $employee
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (InvalidArgumentException $e) {
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Validation error',
                'error' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(400);
            
        } catch (\Exception $e) {
            $this->logger->error('Error updating employee', ['id' => $args['id'], 'error' => $e->getMessage()]);
            
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to update employee',
                'error' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(500);
        }
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int) $args['id'];
            $deleted = $this->service->deleteEmployee($id);
            
            if (!$deleted) {
                $response->getBody()->write(json_encode([
                    'success' => false,
                    'message' => 'Employee not found'
                ]));
                
                return $response->withHeader('Content-Type', 'application/json')
                              ->withStatus(404);
            }
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Employee deleted successfully'
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $this->logger->error('Error deleting employee', ['id' => $args['id'], 'error' => $e->getMessage()]);
            
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to delete employee',
                'error' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(500);
        }
    }

    public function getDepartments(Request $request, Response $response): Response
    {
        try {
            $departments = $this->service->getDepartments();
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Departments retrieved successfully',
                'data' => $departments
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving departments', ['error' => $e->getMessage()]);
            
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to retrieve departments',
                'error' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(500);
        }
    }

    public function getPositions(Request $request, Response $response): Response
    {
        try {
            $positions = $this->service->getPositions();
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Position retrieved successfully',
                'data' => $positions
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving positions', ['error' => $e->getMessage()]);
            
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to retrieve positions',
                'error' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(500);
        }
    }

    public function getStatistics(Request $request, Response $response): Response
    {
        try {
            $stats = $this->service->getStatistics();
            
            $response->getBody()->write(json_encode([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => $stats
            ]));
            
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (\Exception $e) {
            $this->logger->error('Error retrieving statistics', ['error' => $e->getMessage()]);
            
            $response->getBody()->write(json_encode([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ]));
            
            return $response->withHeader('Content-Type', 'application/json')
                          ->withStatus(500);
        }
    }
}