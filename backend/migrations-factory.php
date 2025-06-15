<?php

declare(strict_types=1);

use Doctrine\DBAL\Driver\PDO\MySQL\Driver;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;

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

$migrationsConfig = require __DIR__ . '/migrations-config.php';
$connection = $entityManager->getConnection();

return DependencyFactory::fromEntityManager(
    new PhpFile(__DIR__ . '/migrations-config.php'),
    new ExistingEntityManager($entityManager)
); 