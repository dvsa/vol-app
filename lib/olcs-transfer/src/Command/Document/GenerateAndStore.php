<?php

/**
 * Generate And Store
 *
 * @NOTE This is temporary until all doc generation code is migrated to backend and called as a side effect
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Document;

use Dvsa\Olcs\Transfer\FieldType\Traits\TrafficAreasOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\BusRegOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\CasesOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrfoOrganisationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TransportManagerOptional;

/**
 * @Transfer\RouteName("backend/document/generate-and-store")
 * @Transfer\Method("POST")
 */
class GenerateAndStore extends AbstractCommand
{
    use ApplicationOptional;
    use LicenceOptional;
    use BusRegOptional;
    use CasesOptional;
    use IrfoOrganisationOptional;
    use TransportManagerOptional;
    use TrafficAreasOptional;

    protected $template;

    protected $query = [];

    /**
     * @Transfer\Optional
     */
    protected $knownValues = [];

    protected $category;

    protected $subCategory;

    protected $description;

    protected $isExternal;

    protected $isScan = 0;

    /**
     * @Transfer\Optional
     */
    protected $metadata;

    /**
     * @Transfer\Optional
     */
    protected $dispatch = false;

    /**
     * @Transfer\Optional
     */
    protected $submission;

    /**
     * @Transfer\Optional
     */
    protected $operatingCentre;

    /**
     * @Transfer\Optional
     */
    protected $opposition;

    /**
     * @Transfer\Optional
     */
    protected $issuedDate;

    /**
     * @Transfer\Optional
     */
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
