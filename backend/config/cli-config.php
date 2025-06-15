<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\EntityManager;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$paths = [__DIR__ . '/../src/Entity'];
$isDevMode = true;

$dbParams = [
    'driver'   => 'pdo_mysql',
    'host'     => $_ENV['DB_HOST'] ?? 'localhost',
    'port'     => $_ENV['DB_PORT'] ?? '3306',
    'dbname'   => $_ENV['DB_NAME'] ?? 'employee_management',
    'user'     => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset'  => 'utf8mb4'
];

$config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);
$entityManager = EntityManager::create($dbParams, $config);

return ConsoleRunner::createHelperSet($entityManager); 