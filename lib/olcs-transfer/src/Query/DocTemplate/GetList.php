<?php

/**
 * Get a list of Doc Templates
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\DocTemplate;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTraitOptional;

/**
 * @Transfer\RouteName("backend/doc-template")
 */
class GetList extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTraitOptional;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $category;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $subCategory;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Boolean")
     */
    protected $newTemplatesFirst = false;

    public function getCategory()
    {
        return $this->category;
    }

    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * Get whether to order new templates first
     *
     * @return bool
     */
    public function getNewTemplatesFirst()
    {
        return $this->newTemplatesFirst;
    }
}
