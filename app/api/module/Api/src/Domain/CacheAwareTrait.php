<?php

namespace Dvsa\Olcs\Api\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;

/**
 * Cache Aware Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait CacheAwareTrait
{
    /** @var CacheEncryption */
    protected $cacheService;

    /**
     * @return void
     */
    public function setCache(CacheEncryption $cacheService): void
    {
        $this->cacheService = $cacheService;
    }

    /**
     * @return CacheEncryption
     */
    public function getCache(): CacheEncryption
    {
        return $this->cacheService;
    }

    /**
     * Clear caches for the array of user ids
     *
     *
     * @throws \Exception
     */
    public function clearUserCaches(array $userIds)
    {
        if (!empty($userIds)) {
            foreach (CacheEncryption::USER_CACHES as $cacheType) {
                $this->cacheService->removeCustomItems($cacheType, $userIds);
            }
        }
    }

    /**
     * Clear the caches for users related to an entity (works by default for entities with an array collection
     * made up of user objects.
     *
     * @param $entity
     * @throws \Exception
     */
    public function clearEntityUserCaches($entity)
    {
        /** @var ArrayCollection $users */
        $users = $entity->getUsers();
        $userIds = [];

        /** @var User $user */
        foreach ($users as $user) {
            $userIds[] = $user->getId();
        }

        $this->clearUserCaches($userIds);
    }

    /**
     * Clear the caches related to a licence, for now this just clears caches for users attached to the parent org
     * This code can also be called via \Dvsa\Olcs\Api\Domain\Command\Cache\ClearForLicence command
     *
     * @throws \Exception
     */
    public function clearLicenceCaches(Licence $licence)
    {
        $this->clearOrganisationCaches($licence->getOrganisation());
    }

    /**
     * This code can also be called via \Dvsa\Olcs\Api\Domain\Command\Cache\ClearForOrganisation command
     * Clear the caches related to an organisation. Currently this is only users but in future it could be other things
     * e.g. licences
     *
     * @throws \Exception
     */
    public function clearOrganisationCaches(Organisation $organisation)
    {
        $organisationUsers = $organisation->getOrganisationUsers();

        $userIds = [];

        /** @var OrganisationUser $orgUser */
        foreach ($organisationUsers as $orgUser) {
            $userIds[] = $orgUser->getUser()->getId();
        }

        $this->clearUserCaches($userIds);
    }

    /**
     * Clear a system parameter cache - also clears the parameter list as a whole
     *
     * @return void
     */
    public function clearSystemParamCache(string $id): void
    {
        $this->clearSystemParamListCache();
        $this->cacheService->removeCustomItem(CacheEncryption::SYS_PARAM_IDENTIFIER, $id);
    }

    /**
     * Clears the list of system parameters from the cache
     *
     * @return void
     */
    public function clearSystemParamListCache(): void
    {
        $this->cacheService->removeCustomItem(CacheEncryption::SYS_PARAM_LIST_IDENTIFIER);
    }
}
