<?php

/**
 * Modify
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Workshop\Application;

use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareInterface;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareTrait;
use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Modify
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class Modify extends AbstractHandler implements RepositoryManagerAwareInterface
{
    use RepositoryManagerAwareTrait;

    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        $applicationId = $dto->getApplication();

        // If the user can't access the application
        if ($this->canAccessApplication($applicationId) === false) {
            return false;
        }

        $application = $this->getRepo('Application')->fetchById($applicationId);
        $licenceId = $application->getLicence()->getId();
        $workshops = $this->getWorkshops($dto);
        // Check that the workshops belong to the licence
        foreach ($workshops as $workshop) {
            if ($workshop->getLicence()->getId() !== $licenceId) {
                return false;
            }
        }

        return true;
    }

    protected function getWorkshops($dto)
    {
        return $this->getRepo('Workshop')->fetchByIds($dto->getIds());
    }
}
