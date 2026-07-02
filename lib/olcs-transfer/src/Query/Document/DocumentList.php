<?php

namespace Dvsa\Olcs\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\BusRegOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\CasesOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TransportManagerOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrfoOrganisationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpApplicationOptional;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;

/**
 * @Transfer\RouteName("backend/document")
 */
class DocumentList extends AbstractQuery implements
    OrderedQueryInterface,
    PagedQueryInterface,
    CacheableShortTermQueryInterface
{
    use OrderedTrait;
    use PagedTrait;
    use ApplicationOptional;
    use LicenceOptional;
    use CasesOptional;
    use BusRegOptional;
    use TransportManagerOptional;
    use IrfoOrganisationOptional;
    use IrhpApplicationOptional;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $isExternal;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $category;

    /**
     * @Transfer\Optional
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\FilterEmptyItems")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $documentSubCategory = [];

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"tsw_all", "tsw_self_only", "tsw_exclude_irhp"}})
     */
    protected $showDocs = null;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\I18n\Validator\Alnum")
     */
    protected $format = null;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $onlyUnlinked = null;


    /**
     * Is External
     *
     * @return string
     */
    public function getIsExternal()
    {
        return $this->isExternal;
    }

    /**
     * Get Category
     *
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Get Document Sub Category
     *
     * @return array
     */
    public function getDocumentSubCategory()
    {
        return $this->documentSubCategory;
    }

    /**
     * Get Show Docuements
     *
     * @return string
     */
    public function getShowDocs()
    {
        return $this->showDocs;
    }

    /**
     * Get Format (ie the extension of the document)
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Get onlyUnlinked (only retrieve records with no link to a licence, case, bus reg etc.)
     *
     * @return string
     */
    public function getOnlyUnlinked()
    {
        return $this->onlyUnlinked;
    }
}
