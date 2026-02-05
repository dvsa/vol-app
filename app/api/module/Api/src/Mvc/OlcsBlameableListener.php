<?php

namespace Dvsa\Olcs\Api\Mvc;

use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Gedmo\Blameable\BlameableListener as GedmoBlameableListener;
use Psr\Container\ContainerInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;

/**
 * @psalm-suppress InvalidExtendClass
 * @phpstan-ignore-next-line
 */
class OlcsBlameableListener extends GedmoBlameableListener implements AuthAwareInterface
{
    use AuthAwareTrait;

    /**
     * Injecting instances of AuthService and RepoServiceManager did not work here due to some deeper co-dependency
     * when the app instantiated part of the Doctrine ORM, so ServiceLocator had to be injected instead.
     */
    public function __construct(private ContainerInterface $serviceLocator)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function getFieldValue($meta, $field, $eventAdapter)
    {
        $this->setUserValue(
            $this->getUserValue()
        );

        return $this->callParentGetFieldValue($meta, $field, $eventAdapter);
    }

    /**
     * Get the user value to set on a blameable field
     *
     * @return UserEntity|null
     */
    protected function getUserValue()
    {
        if (!$this->getAuthService()) {
            // set the Auth Service, if not yet set
            $this->setAuthService($this->serviceLocator->get(AuthorizationService::class));
        }
        if (!$this->getUserRepository()) {
            // set the User repository service, if not yet set
            $this->setUserRepository($this->serviceLocator->get('RepositoryServiceManager')->get('User'));
        }

        $masquaradedAsSystemUser = $this->serviceLocator->get(IdentityProviderInterface::class)
            ->getMasqueradedAsSystemUser();

        if ($masquaradedAsSystemUser) {
            $currentUser = $this->getSystemUser();
        } else {
            $currentUser = $this->getCurrentUser();
        }

        return (($currentUser instanceof UserEntity) && !$currentUser->isAnonymous()) ? $currentUser : null;
    }

    /**
     * Call getFieldValue of the parent class - to facilitate unit testing
     *
     * @param object $meta
     * @param string $field
     *
     * @return mixed
     */
    protected function callParentGetFieldValue($meta, $field, mixed $eventAdapter)
    {
        return parent::getFieldValue($meta, $field, $eventAdapter);
    }
}
