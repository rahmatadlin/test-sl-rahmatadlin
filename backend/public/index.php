<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Create container
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(require __DIR__ . '/../config/dependencies.php');
$container = $containerBuilder->build();

// Create app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(
    displayErrorDetails: $_ENV['APP_DEBUG'] === 'true',
    logErrors: true,
    logErrorDetails: true
);

// Add body parsing middleware
$app->addBodyParsingMiddleware();

// Register routes
(require __DIR__ . '/../config/routes.php')($app);

$app->run();