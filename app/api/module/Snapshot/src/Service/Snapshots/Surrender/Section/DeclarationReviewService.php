<?php

namespace Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section;

use Dvsa\Olcs\Api\Entity\Surrender;

class DeclarationReviewService extends AbstractReviewService
{
    /**
     * @param Surrender $surrender
     * @return mixed
     */
    public function getConfigFromData(Surrender $surrender)
    {
        return ['markup' => $this->translateReplace('markup-licence-surrender-declaration', [$surrender->getLicence()->getLicNo()])];
    }
}
