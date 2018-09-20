<?php

namespace Permits\Controller\Config\DataSource;

use Dvsa\Olcs\Transfer\Query\Permits\EcmtPermitFees;
use Olcs\Controller\Config\DataSource\AbstractDataSource;

/**
 * @todo have hooked into the existing way of retrieving the fees, which looks like it may need revising
 */
class FeeList extends AbstractDataSource
{
    const DATA_KEY = 'irhpFeeList';
    protected $dto = EcmtPermitFees::class;
    protected $extraQueryData = ['productReferences' => ['IRHP_GV_APP_ECMT', 'IRHP_GV_ECMT_100_PERMIT_FEE']];
}
