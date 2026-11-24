<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManagerInterface;
use Dvsa\Olcs\Address\Service\Address;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Entity\Types\EncryptedStringType;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Cli\Request\CliRequest;
use Dvsa\Olcs\Cli\Session\NullSaveHandler;
use Dvsa\Olcs\DvsaAddressService\Service\DvsaAddressService;
use Dvsa\OlcsTest\Api\Framework\Support\ApiApplication;
use Dvsa\OlcsTest\Api\Framework\Support\Entrypoint;
use Dvsa\OlcsTest\Api\Framework\Support\FixedToggleService;
use Dvsa\OlcsTest\Api\Framework\Support\FrameworkTestCase;
use Dvsa\OlcsTest\Api\Framework\Support\SystemUser;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Laminas\Cli\ContainerCommandLoader;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Session\Container;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use Monolog\Level;
use Monolog\LogRecord;
use Olcs\Logging\Log\Logger;
use Olcs\Logging\Log\Processor\HideCredentials;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * What booting the api does, for the web and for the command line.
 *
 * Both config/container.php and config/container-cli.php build the container with
 * Laminas\Mvc\Application::init(), which runs every module's onBootstrap. The work those methods do is
 * invisible from the outside but everything depends on it, including every AWS Batch job, which starts
 * from container-cli.php. Replace Application::init() without carrying each of these across and nothing
 * fails loudly: soft-deleted rows reappear in queries, encrypted columns cannot be read, logs lose their
 * user and credential redaction. This test names each one, so the replacement bootstrap has to keep them.
 */
final class BootstrapTest extends FrameworkTestCase
{
    /**
     * The cipher text of PLAINTEXT under ApiApplication::ENCRYPTION_KEY with the current settings:
     * AES-CBC through OpenSSL with an all-zero IV (see Api Module::initDoctrineEncrypterType). Every
     * encrypted column in the database was written this way, so a bootstrap that configures the cipher
     * any differently can no longer read them.
     */
    private const string PLAINTEXT = 'framework test secret';
    private const string CIPHERTEXT = 'HBE7BHmwLrV3iOpAbgeaCet/gD/Lb5Z9/0OKkQF31mU=';

    #[DataProvider('entrypoints')]
    public function testSoftDeletedRowsAreFilteredOutOfEveryQuery(Entrypoint $entrypoint): void
    {
        $entityManager = $this->boot($entrypoint)->serviceManager->get('doctrine.entitymanager.orm_default');
        assert($entityManager instanceof EntityManagerInterface);

        $this->assertTrue($entityManager->getFilters()->isEnabled('soft-deleteable'));
    }

    #[DataProvider('entrypoints')]
    public function testEncryptedColumnsUseTheExistingCipher(Entrypoint $entrypoint): void
    {
        $entityManager = $this->boot($entrypoint)->serviceManager->get('doctrine.entitymanager.orm_default');
        assert($entityManager instanceof EntityManagerInterface);
        $platform = $entityManager->getConnection()->getDatabasePlatform();

        $type = Type::getType(EncryptedStringType::TYPE);

        $this->assertSame(self::CIPHERTEXT, $type->convertToDatabaseValue(self::PLAINTEXT, $platform));
        $this->assertSame(self::PLAINTEXT, $type->convertToPHPValue(self::CIPHERTEXT, $platform));
    }

    #[DataProvider('entrypoints')]
    public function testTheStaticLoggerIsTheConfiguredLoggerAndRedactsCredentials(Entrypoint $entrypoint): void
    {
        $app = $this->boot($entrypoint);

        $this->assertSame($app->serviceManager->get('Logger'), Logger::getLogger());

        Logger::info('framework test', ['Authorization' => 'Bearer abc', 'password' => 'hunter2']);

        $record = $this->lastRecord($app);
        $this->assertSame('framework test', $record->message);
        $this->assertSame(
            ['Authorization' => HideCredentials::REPLACE_WITH, 'password' => '*** HIDDEN PASSWORD ***'],
            $record->context
        );
    }

    /**
     * @return iterable<string, array{Entrypoint, string}>
     */
    public static function logUsers(): iterable
    {
        // A web request with no Authorization header is the anonymous user
        yield 'web' => [Entrypoint::Web, User::ANON_USERNAME];
        // The command line always runs as the system user
        yield 'cli' => [Entrypoint::Cli, SystemUser::LOGIN_ID];
    }

    #[DataProvider('logUsers')]
    public function testEveryLogRecordCarriesTheUserTheRequestRunsAs(Entrypoint $entrypoint, string $loginId): void
    {
        $app = $this->boot($entrypoint);

        $identity = $app->serviceManager->get(AuthorizationService::class)->getIdentity();
        $this->assertSame($loginId, $identity->getUser()->getLoginId());

        Logger::info('framework test');

        $this->assertSame($loginId, $this->lastRecord($app)->extra['userId']);
    }

    #[DataProvider('entrypoints')]
    public function testUserErrorsAreLoggedInsteadOfHaltingTheRequest(Entrypoint $entrypoint): void
    {
        $app = $this->boot($entrypoint);

        // Called directly: trigger_error(E_USER_ERROR) is itself deprecated on PHP 8.4
        $handler = set_error_handler(static fn(): bool => false);
        restore_error_handler();
        $this->assertIsCallable($handler);

        $this->assertTrue($handler(E_USER_ERROR, 'framework test', 'file.php', 7));

        $record = $this->lastRecord($app);
        $this->assertSame(Level::Error, $record->level);
        $this->assertSame('TOLERATED_USER_ERROR: framework test', $record->message);
        $this->assertSame('tolerated-user-error', $record->context['tag']);
    }

    /**
     * @return iterable<string, array{list<string>, class-string}>
     */
    public static function addressServiceToggles(): iterable
    {
        yield 'new address service on' => [[FeatureToggle::USE_NEW_ADDRESS_SERVICE], DvsaAddressService::class];
        yield 'new address service off' => [[], Address::class];
    }

    /**
     * @param list<string> $enabledToggles
     */
    #[DataProvider('addressServiceToggles')]
    public function testTheAddressServiceFollowsItsFeatureToggle(array $enabledToggles, string $expected): void
    {
        // Stand-ins for both services: creating the real DvsaAddressService fetches an OAuth token
        $services = [DvsaAddressService::class => m::mock(DvsaAddressService::class), Address::class => m::mock(Address::class)];

        $app = ApiApplication::bootstrap(
            Entrypoint::Web,
            static function (ServiceManager $serviceManager) use ($enabledToggles, $services): void {
                $serviceManager->setService(ToggleService::class, new FixedToggleService($enabledToggles));

                foreach ($services as $name => $service) {
                    $serviceManager->setService($name, $service);
                }
            }
        );

        $this->assertSame($services[$expected], $app->serviceManager->get('AddressService'));
    }

    public function testTheWebRequestIsTheHttpRequest(): void
    {
        $this->assertInstanceOf(HttpRequest::class, $this->boot(Entrypoint::Web)->serviceManager->get('Request'));
    }

    public function testTheCommandLineRequestIsTheCliRequest(): void
    {
        $this->assertInstanceOf(CliRequest::class, $this->boot(Entrypoint::Cli)->serviceManager->get('Request'));
    }

    public function testTheCommandLineNeverSavesSessions(): void
    {
        $this->boot(Entrypoint::Cli);

        $this->assertInstanceOf(NullSaveHandler::class, Container::getDefaultManager()->getSaveHandler());
    }

    /**
     * Each AWS Batch job is one of these, run as `vendor/bin/laminas --container=config/container-cli.php <name>`.
     *
     * Loaded the way laminas-cli loads them: from the container if it has the class, otherwise constructed
     * directly (the doctrine-migrations commands, whose DependencyFactory sits under a `dependencies` config
     * key that the laminas-mvc ServiceManager does not read).
     */
    public function testEveryCommandLineCommandCanBeCreated(): void
    {
        $serviceManager = $this->boot(Entrypoint::Cli)->serviceManager;
        $commands = $serviceManager->get('config')['laminas-cli']['commands'];
        $this->assertNotEmpty($commands);

        $loader = new ContainerCommandLoader($serviceManager, $commands);
        $failures = [];

        foreach (array_keys($commands) as $name) {
            try {
                $this->assertSame($name, $loader->get($name)->getName());
            } catch (\Throwable $e) {
                $failures[$name] = $e::class . ': ' . $e->getMessage();
            }
        }

        $this->assertSame([], $failures);
    }

    /**
     * @return iterable<string, array{Entrypoint}>
     */
    public static function entrypoints(): iterable
    {
        foreach (Entrypoint::cases() as $entrypoint) {
            yield $entrypoint->value => [$entrypoint];
        }
    }

    private function boot(Entrypoint $entrypoint): ApiApplication
    {
        if ($entrypoint === Entrypoint::Web) {
            return ApiApplication::bootstrap($entrypoint);
        }

        return ApiApplication::bootstrap($entrypoint, SystemUser::answerTheBootstrapLookup(...));
    }

    private function lastRecord(ApiApplication $app): LogRecord
    {
        $records = $app->logs->getRecords();
        $this->assertNotEmpty($records);

        return $records[array_key_last($records)];
    }
}
