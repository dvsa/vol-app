<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Dvsa\Olcs\Transfer\Query\Fee\GetLatestFeeType;

/**
 * Fee Type Data Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FeeTypeDataService extends AbstractDataService
{
    public const FEE_TYPE_APP = 'APP';

    public const FEE_TYPE_VAR = 'VAR';

    public const FEE_TYPE_CONT = 'CONT';

    public const FEE_TYPE_GRANTINT = 'GRANTINT';

    public const FEE_TYPE_BUSAPP = 'BUSAPP';

    public const FEE_TYPE_BUSVAR = 'BUSVAR';
}
