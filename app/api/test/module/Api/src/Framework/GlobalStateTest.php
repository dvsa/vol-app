<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework;

use Doctrine\DBAL\Types\Type;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepository;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\OlcsTest\Api\Framework\Support\ApiApplication;
use Dvsa\OlcsTest\Api\Framework\Support\Entrypoint;
use Dvsa\OlcsTest\Api\Framework\Support\GlobalState;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Booting the api and restoring GlobalState leaves the process as it found it.
 *
 * The Framework tests boot the real application inside the unit suite, which CI runs in random order. If
 * this fails, something the boot does now outlives the test — add it to GlobalState.
 */
final class GlobalStateTest extends MockeryTestCase
{
    /**
     * @return iterable<string, array{Entrypoint}>
     */
    public static function entrypoints(): iterable
    {
        foreach (Entrypoint::cases() as $entrypoint) {
            yield $entrypoint->value => [$entrypoint];
        }
    }

    #[DataProvider('entrypoints')]
    public function testRestoringPutsBackEverythingTheBootChanged(Entrypoint $entrypoint): void
    {
        $before = $this->observe();
        $state = GlobalState::capture();

        $this->boot($entrypoint);
        // Logging is what starts a session (the SessionId processor)
        Logger::info('framework test');

        $during = $this->observe();

        // Proves the test is looking at state the boot really changes
        foreach (['error handler', 'exception handler', 'logger', 'log user id', 'doctrine types', 'session status'] as $item) {
            $this->assertNotSame($before[$item], $during[$item], $item . ' was not changed by the boot');
        }

        $state->restore();

        $this->assertSame($before, $this->observe());
    }

    /**
     * @return array<string, mixed>
     */
    private function observe(): array
    {
        $errorHandler = set_error_handler(static fn(): bool => false);
        restore_error_handler();
        $exceptionHandler = set_exception_handler(null);
        restore_exception_handler();

        return [
            'error handler' => $errorHandler === null ? null : spl_object_id($errorHandler),
            'exception handler' => $exceptionHandler === null ? null : spl_object_id($exceptionHandler),
            'logger' => (new \ReflectionProperty(Logger::class, 'logger'))->getValue(),
            'log user id' => (new \ReflectionProperty(\Olcs\Logging\Log\Processor\UserId::class, 'userId'))->getValue(),
            'doctrine types' => array_map(
                spl_object_id(...),
                (new \ReflectionProperty(\Doctrine\DBAL\Types\TypeRegistry::class, 'instances'))->getValue(Type::getTypeRegistry())
            ),
            'session manager' => (new \ReflectionProperty(\Laminas\Session\AbstractContainer::class, 'defaultManager'))->getValue(),
            'session status' => session_status(),
            'session id' => session_id(),
            'session save handler' => ini_get('session.save_handler'),
            'autoloaders' => count(spl_autoload_functions()),
        ];
    }

    private function boot(Entrypoint $entrypoint): void
    {
        if ($entrypoint === Entrypoint::Web) {
            ApiApplication::bootstrap($entrypoint);

            return;
        }

        $systemUser = new User('pid', User::USER_TYPE_INTERNAL);
        $systemUser->setLoginId('system-user');

        $userRepository = m::mock(UserRepository::class);
        $userRepository->expects('fetchById')->with(IdentityProviderInterface::SYSTEM_USER)->andReturn($systemUser);

        ApiApplication::bootstrap($entrypoint, static function (ServiceManager $serviceManager) use ($userRepository): void {
            $repositories = $serviceManager->get('RepositoryServiceManager');
            assert($repositories instanceof RepositoryServiceManager);
            $repositories->setAllowOverride(true);
            $repositories->setService('User', $userRepository);
        });
    }
}
