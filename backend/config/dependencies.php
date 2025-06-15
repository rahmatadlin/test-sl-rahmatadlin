<?php

use App\Repository\EmployeeRepository;
use App\Service\EmployeeService;
use App\Controller\EmployeeController;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

return [
    // Logger
    LoggerInterface::class => function () {
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
        return $logger;
    },

    // Database
    EntityManager::class => require __DIR__ . '/database.php',

    // Repositories
    EmployeeRepository::class => function (ContainerInterface $c) {
        return new EmployeeRepository($c->get(EntityManager::class));
    },

    // Services
    EmployeeService::class => function (ContainerInterface $c) {
        return new EmployeeService($c->get(EmployeeRepository::class));
    },

    // Controllers
    EmployeeController::class => function (ContainerInterface $c) {
        return new EmployeeController(
            $c->get(EmployeeService::class),
            $c->get(LoggerInterface::class)
        );
    },
];