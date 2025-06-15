<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

return function () {
    $config = ORMSetup::createAttributeMetadataConfiguration(
        paths: [__DIR__ . '/../src/Entity'],
        isDevMode: $_ENV['APP_DEBUG'] === 'true',
    );

    $connectionParams = [
        'driver'   => 'pdo_mysql',
        'host'     => $_ENV['DB_HOST'],
        'port'     => $_ENV['DB_PORT'],
        'dbname'   => $_ENV['DB_NAME'],
        'user'     => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASS'],
        'charset'  => 'utf8mb4',
    ];

    $connection = DriverManager::getConnection($connectionParams);
    
    return new EntityManager($connection, $config);
};