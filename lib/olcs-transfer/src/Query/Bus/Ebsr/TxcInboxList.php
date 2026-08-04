<?php

namespace Dvsa\Olcs\Transfer\Query\Bus\Ebsr;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;

/**
 * Class TxcInboxList
 * @Transfer\RouteName("backend/txc-inbox")
 */
class TxcInboxList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;

    /**
     * @var string
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {
     *              "breg_s_admin","breg_s_cancellation","breg_s_cancelled","breg_s_cns","breg_s_curt","breg_s_expired",
     *              "breg_s_new","breg_s_refused","breg_s_registered","breg_s_revoked","breg_s_surr","breg_s_var",
     *              "breg_s_withdrawn"
     *          }
     *      }
     * )
     */
    protected $status;

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
