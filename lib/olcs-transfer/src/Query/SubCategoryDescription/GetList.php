<?php

/**
 * Get a list of Sub Category Descriptions
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\SubCategoryDescription;

use Dvsa\Olcs\Transfer\Query\PublicQueryCacheInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;

/**
 * @Transfer\RouteName("backend/subcategory-description")
 */
class GetList extends AbstractQuery implements
    \Dvsa\Olcs\Transfer\Query\OrderedQueryInterface,
    CacheableLongTermQueryInterface,
    PublicQueryCacheInterface
{
    use \Dvsa\Olcs\Transfer\Query\OrderedTraitOptional;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     */
    protected $subCategory;

    public function getSubCategory()
    {
        return $this->subCategory;
    }
}
