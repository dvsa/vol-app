<?php

/**
 * Get a list of RefData
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\RefData;

use Dvsa\Olcs\Transfer\Query\PublicQueryCacheInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;

/**
 * @Transfer\RouteName("backend/ref-data")
 */
class RefDataList extends AbstractQuery implements CacheableLongTermQueryInterface, PublicQueryCacheInterface
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Filter("Laminas\Filter\StringToLower")
     */
    protected $refDataCategory;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Filter("Laminas\Filter\StringToLower")
     * @Transfer\Validator("Laminas\Validator\InArray",options={"haystack": {"en", "cy"}})
     */
    protected $language;

    public function getRefDataCategory()
    {
        return $this->refDataCategory;
    }

    public function getLanguage()
    {
        return $this->language;
    }
}
