<?php

namespace Dvsa\Olcs\Api\Domain;

/**
 * Repository Manager Aware Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface RepositoryManagerAwareInterface
{
    public function setRepoManager(RepositoryServiceManager $repoManager);

    public function getRepoManager();
}
