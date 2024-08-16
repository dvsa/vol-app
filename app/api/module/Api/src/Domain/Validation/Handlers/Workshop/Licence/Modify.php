<?php

/**
 * Modify
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Workshop\Licence;

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
        $licenceId = $dto->getLicence();

        if (!$licenceId) {
            $applicationId = $dto->getApplication();
            if (!$applicationId) {
                return false;
            }
            $application = $this->getRepo('Application')->fetchById($applicationId);
            $licenceId = $application->getLicence()->getId();
        }

        // If the user can't access the licence
        if ($this->canAccessLicence($licenceId) === false) {
            return false;
        }

        $workshops = $this->getWorkshops($dto);

        // Check that the workshops belong to the licence
        foreach ($workshops as $workshop) {
            if ($workshop->getLicence()->getId() != $licenceId) {
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
