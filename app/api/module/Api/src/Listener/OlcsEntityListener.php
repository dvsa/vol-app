<?php

namespace Dvsa\Olcs\Api\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Persistence\NotifyPropertyChanged;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Entity;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Psr\Container\ContainerInterface;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.com>
 */
class OlcsEntityListener implements EventSubscriber, AuthAwareInterface, FactoryInterface
{
    use AuthAwareTrait;

    /** @var  ContainerInterface */
    private $sl;

    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'preSoftDelete',
        ];
    }

    /**
     * Process event - preSoftDelete
     *
     * @param LifecycleEventArgs $args Event arguments
     *
     * @return void
     */
    public function preSoftDelete(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        $om = $args->getEntityManager();

        $meta = $om->getClassMetadata($object::class);

        $this->updateField($object, $om, $meta, 'lastModifiedBy', $this->getModifiedByUser());
    }

    /**
     * Updates a field
     *
     * @param \Dvsa\Olcs\Api\Entity\*             $object Entity
     * @param \Doctrine\ORM\EntityManager         $om     Entity Manager
     * @param \Doctrine\ORM\Mapping\ClassMetadata $meta   Class meta data
     * @param string                              $field  Field Name
     * @param mixed                               $value  New value
     *
     * @return void
     */
    private function updateField($object, $om, $meta, $field, mixed $value)
    {
        if (!method_exists($object, 'set' . ucfirst($field))) {
            return;
        }

        $property = $meta->getReflectionProperty($field);
        $oldValue = $property->getValue($object);

        if ($meta->hasAssociation($field) && $value) {
            $om->persist($value);
        }
        $property->setValue($object, $value);

        $uow = $om->getUnitOfWork();
        if ($object instanceof NotifyPropertyChanged) {
            $uow->propertyChanged($object, $field, $oldValue, $value);
        }

        $uow->scheduleExtraUpdate(
            $object,
            [
                $field => [$oldValue, $value],
            ]
        );
    }

    /**
     * Get the user value
     *
     * @return Entity\User\User|null
     */
    private function getModifiedByUser()
    {
        if ($this->getAuthService() === null) {
            $this->setAuthService($this->sl->get(AuthorizationService::class));
        }
        if ($this->getUserRepository() === null) {
            $this->setUserRepository($this->sl->get('RepositoryServiceManager')->get('User'));
        }

        $masqueradedAsSystemUser = $this->sl->get(IdentityProviderInterface::class)->getMasqueradedAsSystemUser();

        if ($masqueradedAsSystemUser) {
            $currentUser = $this->getSystemUser();
        } else {
            $currentUser = $this->getCurrentUser();
        }

        return (
            (
                $currentUser instanceof Entity\User\User
                && $currentUser->isAnonymous() === false
            )
            ? $currentUser
            : null
        );
    }
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->sl = $container;
        return $this;
    }
}
