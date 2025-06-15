<?php

declare(strict_types=1);

use Doctrine\DBAL\Driver\PDO\MySQL\Driver;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$paths = [__DIR__ . '/src/Entity'];
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

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);
$entityManager = EntityManager::create($dbParams, $config);

return [
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
        'version_column_name' => 'version',
        'version_column_length' => 191,
        'executed_at_column_name' => 'executed_at',
        'execution_time_column_name' => 'execution_time',
    ],

    'migrations_paths' => [
        'DoctrineMigrations' => __DIR__ . '/migrations',
    ],

    'all_or_nothing' => true,
    'check_database_platform' => true,
    'organize_migrations' => 'none',
    'connection' => null,
    'em' => null,
]; 