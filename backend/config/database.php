<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configure cache
$cache = new FilesystemAdapter('', 0, __DIR__ . '/../var/cache');

$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: [__DIR__ . '/../src/Entity'],
    isDevMode: true,
    proxyDir: __DIR__ . '/../var/cache/proxies',
    cache: $cache
);

$dbParams = [
    'driver'   => 'pdo_mysql',
    'host'     => $_ENV['DB_HOST'] ?? 'localhost',
    'port'     => $_ENV['DB_PORT'] ?? '3306',
    'dbname'   => $_ENV['DB_NAME'] ?? 'test_sl',
    'user'     => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset'  => 'utf8mb4'
];

$connection = DriverManager::getConnection($dbParams, $config);
return new EntityManager($connection, $config); 