<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Logger;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Cases\Cases;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Transfer\Command\Audit\ReadOrganisation;
use Dvsa\Olcs\Transfer\Command\Audit\ReadLicence;
use Dvsa\Olcs\Transfer\Command\Audit\ReadCase;
use Dvsa\Olcs\Transfer\Command\Audit\ReadApplication;
use Dvsa\Olcs\Transfer\Command\Audit\ReadBusReg;
use Dvsa\Olcs\Transfer\Command\Audit\ReadTransportManager;
use Dvsa\Olcs\Transfer\Command\Audit\ReadIrhpApplication;
use Dvsa\Olcs\Api\Entity\User\Permission;
use RuntimeException;
use Dvsa\Olcs\Api\Rbac\Identity;
use Dvsa\Olcs\Api\Entity\User\User;

/**
 * @see EntityAccessLoggerFactory
 * @see \Dvsa\OlcsTest\Api\Logger\EntityAccessLoggerTest
 */
class EntityAccessLogger
{
    public const ENTITY_AUDIT_LOG_COMMAND_MAP = [
        Organisation::class => ReadOrganisation::class,
        Licence::class => ReadLicence::class,
        Cases::class => ReadCase::class,
        Application::class => ReadApplication::class,
        BusReg::class => ReadBusReg::class,
        TransportManager::class => ReadTransportManager::class,
        IrhpApplication::class => ReadIrhpApplication::class,
    ];

    protected const PERMISSION_ENTITIES_MAP = [
        Permission::INTERNAL_USER => [
            Organisation::class,
            Licence::class,
            Cases::class,
            Application::class,
            BusReg::class,
            TransportManager::class,
            IrhpApplication::class,
        ],
        Permission::PARTNER_USER => [
            Licence::class,
        ],
        Permission::PARTNER_ADMIN => [
            Licence::class,
        ],
    ];

    /**
     * @var CommandHandlerManager
     */
    protected $commandHandler;

    /**
     * @var AuthorizationService
     */
    protected $authService;

    public function __construct(AuthorizationService $authorisationService, CommandHandlerManager $commandHandler)
    {
        $this->authService = $authorisationService;
        $this->commandHandler = $commandHandler;
    }

    /**
     * @param object $entity
     * @return bool
     * @throws RuntimeException
     */
    public function logAccessToEntity($entity): bool
    {
        $user = ($identity = $this->authService->getIdentity()) instanceof Identity ? $identity->getUser() : null;
        if (! ($user instanceof User) || $user->isAnonymous()) {
            return false;
        }

        foreach (static::PERMISSION_ENTITIES_MAP as $permission => $entitiesEnabledForPermission) {
            if ($this->authService->isGranted($permission)) {
                $entityClassRef = $entity::class;
                $entityLogCommandRef = static::ENTITY_AUDIT_LOG_COMMAND_MAP[$entityClassRef] ?? null;
                if (! in_array($entityClassRef, $entitiesEnabledForPermission, true)) {
                    throw new RuntimeException('Cannot create audit read for entity, no DTO is defined');
                }
                $dto = call_user_func([$entityLogCommandRef, 'create'], ['id' => $entity->getId()]);
                $this->commandHandler->handleCommand($dto);
                return true;
            }
        }

        return false;
    }
}
