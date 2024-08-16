<?php

namespace Dvsa\Olcs\Api\Domain;

/**
 * Repository Manager Aware Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait RepositoryManagerAwareTrait
{
    /**
     * @var RepositoryServiceManager
     */
    protected $repoManager;

    public function setRepoManager(RepositoryServiceManager $repoManager)
    {
        $this->repoManager = $repoManager;
    }

    public function getRepoManager()
    {
        return $this->repoManager;
    }

    public function getRepo($name)
    {
        return $this->getRepoManager()->get($name);
    }
}
