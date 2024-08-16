<?php

namespace Dvsa\Olcs\Api\Service\Qa;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;

class QaEntityProvider
{
    /**
     * Create service instance
     *
     *
     * @return QaEntityProvider
     */
    public function __construct(private readonly IrhpApplicationRepository $irhpApplicationRepo, private readonly IrhpPermitApplicationRepository $irhpPermitApplicationRepo)
    {
    }

    /**
     * Return an implementation of QaEntityInterface based on the provided entity ids
     *
     * @param int $irhpApplicationId
     * @param int|null $irhpPermitApplicationId
     *
     * @return QaEntityInterface
     *
     * @throws NotFoundException if no entity is found or entity ids are mismatched
     */
    public function get($irhpApplicationId, $irhpPermitApplicationId)
    {
        $irhpApplication = $this->irhpApplicationRepo->fetchById($irhpApplicationId);
        $qaEntity = $irhpApplication;

        if (!is_null($irhpPermitApplicationId)) {
            $irhpPermitApplication = $this->irhpPermitApplicationRepo->fetchById($irhpPermitApplicationId);

            if ($irhpPermitApplication->getIrhpApplication() !== $irhpApplication) {
                throw new NotFoundException('Mismatched IrhpApplication and IrhpPermitApplication');
            }

            $qaEntity = $irhpPermitApplication;
        }

        return $qaEntity;
    }
}
