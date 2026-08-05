<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Support;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\DbQueryServiceManager;
use Dvsa\Olcs\Api\Domain\DbQueryServiceManagerFactory;
use Dvsa\Olcs\Api\Domain\QueryBuilder;
use Dvsa\Olcs\Api\Domain\QueryPartialServiceManager;
use Dvsa\Olcs\Api\Domain\QueryPartialServiceManagerFactory;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManagerFactory;
use Laminas\ServiceManager\ServiceManager;

/**
 * Builds the minimal real object graph needed to run repositories against the
 * local development database: an EntityManager with the production entity
 * metadata, custom DBAL types and DQL functions (reusing the standalone
 * phpstan-doctrine loader), plus the query partial / db query / repository
 * service managers wired exactly as RepositoryFactory expects - no MVC
 * bootstrap, no config/autoload environment needed.
 */
final class Database
{
    private static ?EntityManager $entityManager = null;

    private static ?ServiceManager $container = null;

    private static ?string $unavailableReason = null;

    private static bool $availabilityChecked = false;

    public static function isAvailable(): bool
    {
        if (!self::$availabilityChecked) {
            self::$availabilityChecked = true;

            try {
                self::entityManager()->getConnection()->executeQuery('SELECT 1');
            } catch (\Throwable $e) {
                self::$unavailableReason = $e->getMessage();
            }
        }

        return self::$unavailableReason === null;
    }

    public static function unavailableReason(): string
    {
        return self::$unavailableReason ?? '';
    }

    public static function entityManager(): EntityManager
    {
        if (self::$entityManager === null) {
            $appRoot = dirname(__DIR__, 4);

            // Configuration (metadata, custom types, DQL functions) shared with
            // phpstan-doctrine; only the connection differs.
            $template = require $appRoot . '/phpstan-object-manager.php';
            $configuration = $template->getConfiguration();

            // Keep Liquibase's own bookkeeping tables out of schema introspection:
            // DATABASECHANGELOGLOCK has a BIT column that DBAL cannot map, and
            // neither table will ever correspond to an entity.
            $configuration->setSchemaAssetsFilter(
                static fn ($asset): bool => !str_starts_with(
                    is_string($asset) ? $asset : $asset->getName(),
                    'DATABASECHANGELOG',
                ),
            );

            $connection = DriverManager::getConnection(
                [
                    'driver' => 'pdo_mysql',
                    'host' => getenv('VOL_TEST_DB_HOST') ?: '127.0.0.1',
                    'port' => (int) (getenv('VOL_TEST_DB_PORT') ?: 3306),
                    'user' => getenv('VOL_TEST_DB_USER') ?: 'root',
                    'password' => getenv('VOL_TEST_DB_PASSWORD') ?: 'olcs',
                    'dbname' => getenv('VOL_TEST_DB_NAME') ?: 'olcs_be',
                    'serverVersion' => '8.0',
                    'charset' => 'utf8',
                ],
                $configuration,
            );

            self::$entityManager = new EntityManager($connection, $configuration);
        }

        return self::$entityManager;
    }

    /**
     * Fetch a repository by its short service name (e.g. 'LicenceVehicle'),
     * built by the production RepositoryFactory.
     */
    public static function repository(string $name): mixed
    {
        return self::container()->get('RepositoryServiceManager')->get($name);
    }

    private static function container(): ServiceManager
    {
        if (self::$container === null) {
            $appRoot = dirname(__DIR__, 4);
            $entityManager = self::entityManager();
            $moduleConfig = require $appRoot . '/module/Api/config/module.config.php';

            $container = new ServiceManager();
            $container->setService('config', $moduleConfig);
            $container->setService('doctrine.entitymanager.orm_default', $entityManager);

            $queryPartialManager = new QueryPartialServiceManager(
                $container,
                $moduleConfig[QueryPartialServiceManagerFactory::CONFIG_KEY],
            );
            $container->setService('QueryPartialServiceManager', $queryPartialManager);
            $container->setService('QueryBuilder', new QueryBuilder($queryPartialManager));
            $container->setService(
                'DbQueryServiceManager',
                new DbQueryServiceManager($container, $moduleConfig[DbQueryServiceManagerFactory::CONFIG_KEY]),
            );
            $container->setService(
                'RepositoryServiceManager',
                new RepositoryServiceManager($container, $moduleConfig[RepositoryServiceManagerFactory::CONFIG_KEY]),
            );

            self::$container = $container;
        }

        return self::$container;
    }
}
