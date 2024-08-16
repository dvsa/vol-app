<?php

namespace Dvsa\Olcs\Api\Domain\Command\Document;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\BusRegOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\CasesOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrfoOrganisationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TrafficAreasOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TransportManagerOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\PrintOptional;

/**
 * Generate And Store
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GenerateAndStore extends AbstractCommand
{
    use ApplicationOptional;
    use LicenceOptional;
    use BusRegOptional;
    use CasesOptional;
    use IrfoOrganisationOptional;
    use IrhpApplicationOptional;
    use TransportManagerOptional;
    use TrafficAreasOptional;
    use PrintOptional;

    protected $template;

    protected $query = [];

    protected $knownValues = [];

    protected $category;

    protected $subCategory;

    protected $description;

    protected $isExternal;

    protected $isScan = 0;

    protected $metadata;

    protected $dispatch = false;

    protected $submission;

    protected $operatingCentre;

    protected $opposition;

    protected $issuedDate;

    protected $disableBookmarks;

    /**
     * @return mixed
     */
    public function getDisableBookmarks()
    {
        return $this->disableBookmarks;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getKnownValues()
    {
        return $this->knownValues;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return mixed
     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getIsExternal()
    {
        return $this->isExternal;
    }

    /**
     * @return int
     */
    public function getIsScan()
    {
        return $this->isScan;
    }

    /**
     * @return mixed
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @return boolean
     */
    public function getDispatch()
    {
        return $this->dispatch;
    }

    /**
     * @return mixed
     */
    public function getSubmission()
    {
        return $this->submission;
    }

    /**
     * @return mixed
     */
    public function getOperatingCentre()
    {
        return $this->operatingCentre;
    }

    /**
     * @return mixed
     */
    public function getOpposition()
    {
        return $this->opposition;
    }

    /**
     * @return mixed
     */
    public function getIssuedDate()
    {
        return $this->issuedDate;
    }
}
