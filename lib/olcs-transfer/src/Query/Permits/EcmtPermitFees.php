<?php

/**
 * Get Ecmt Permit Fees
 *
 * @author Kollol Shamsuddin <kol.shamsudin@capgemini.com>
 * @author Jason De Jonge <jason.de-jonge@capgemini.com>
 */

namespace Dvsa\Olcs\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\PublicQueryCacheInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;

/**
 * @Transfer\RouteName("backend/permits/ecmt-permit-fees")
 */
class EcmtPermitFees extends AbstractQuery implements CacheableLongTermQueryInterface, PublicQueryCacheInterface
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\FilterEmptyItems")
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     */
    protected $productReferences;

    /**
     * @return mixed
     */
    public function getProductReferences()
    {
        return $this->productReferences;
    }
}
