<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;

$paths = [__DIR__ . '/../src/Entity'];
$isDevMode = true;

$dbParams = [
    'driver'   => 'pdo_mysql',
    'host'     => $_ENV['DB_HOST'] ?? 'localhost',
    'port'     => $_ENV['DB_PORT'] ?? '3306',
    'dbname'   => $_ENV['DB_NAME'] ?? 'test_sl',
    'user'     => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset'  => 'utf8mb4'
];

// Register annotation loader
AnnotationRegistry::registerLoader('class_exists');

$config = ORMSetup::createAnnotationMetadataConfiguration($paths, $isDevMode);
$connection = DriverManager::getConnection($dbParams, $config);

return new EntityManager($connection, $config); 