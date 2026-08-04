<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Goods or PSV Optional.
 * Null allowed as a string to prevent returning both goods AND psv options in filter
 *
 */
trait GoodsOrPsvOptional
{
    /**
     * @var String
     *
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={"haystack": {"lcat_gv","lcat_psv", "lcat_permit", "NULL"}}
     * )
     */
    protected $goodsOrPsv = null;

    /**
     * @return string
     */
    public function getGoodsOrPsv()
    {
        return $this->goodsOrPsv;
    }
}
