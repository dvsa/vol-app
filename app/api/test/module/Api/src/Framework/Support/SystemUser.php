<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

use Dvsa\Olcs\Api\Domain\Repository\User as UserRepository;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;

/**
 * The one database read the command line makes while it bootstraps.
 *
 * config/container-cli.php's application runs as the system user, which it fetches by id while bootstrapping
 * (for the log user id and the identity). The unit suite has no database, so the user repository answers
 * that one call, exactly once.
 */
final class SystemUser
{
    public const string LOGIN_ID = 'system-user';

    /**
     * Call from ApiApplication::bootstrap()'s $configure closure when booting Entrypoint::Cli.
     */
    public static function answerTheBootstrapLookup(ServiceManager $serviceManager): void
    {
        $systemUser = new User('pid', User::USER_TYPE_INTERNAL);
        $systemUser->setLoginId(self::LOGIN_ID);

        $userRepository = m::mock(UserRepository::class);
        $userRepository->expects('fetchById')->with(IdentityProviderInterface::SYSTEM_USER)->andReturn($systemUser);

        $repositories = $serviceManager->get('RepositoryServiceManager');
        assert($repositories instanceof RepositoryServiceManager);
        $repositories->setAllowOverride(true);
        $repositories->setService('User', $userRepository);
        $repositories->setAllowOverride(false);
    }
}
