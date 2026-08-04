<?php

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/application/single/upload-evidence")
 * @Transfer\Method("PUT")
 */
final class UploadEvidence extends AbstractCommand
{
    use Identity;

    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\FilterEmptyItems")
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\Partial\OperatingCentreEvidence")
     * @Transfer\Optional
     */
    protected $operatingCentres = [];

    /**
     * @Transfer\Optional
     */
    protected $financialEvidence;

    /**
     * @Transfer\Optional
     */
    protected $supportingEvidence;

    /**
     * Get operating centres
     *
     * @return array
     */
    public function getOperatingCentres()
    {
        return $this->operatingCentres;
    }

    /**
     * Get financial evidence
     *
     * @return bool
     */
    public function getFinancialEvidence()
    {
        return $this->financialEvidence;
    }


    /**
     * Get supporting evidence
     *
     * @return bool
     */
    public function getSupportingEvidence(): ?bool
    {
        return $this->supportingEvidence;
    }
}
