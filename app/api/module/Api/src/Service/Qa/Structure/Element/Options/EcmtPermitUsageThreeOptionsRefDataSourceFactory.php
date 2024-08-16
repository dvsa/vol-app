<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options;

use Dvsa\Olcs\Api\Entity\System\RefData;

class EcmtPermitUsageThreeOptionsRefDataSourceFactory extends AbstractEcmtPermitUsageRefDataSourceFactory
{
    public const TRANSFORMATIONS = [
        RefData::ECMT_PERMIT_USAGE_THREE_BOTH => [
            EcmtPermitUsageRefDataSource::LABEL_KEY =>
                'qanda.ecmt.permit-usage.three-options.option.both.label',
        ],
        RefData::ECMT_PERMIT_USAGE_THREE_CROSS_TRADE_ONLY => [
            EcmtPermitUsageRefDataSource::LABEL_KEY =>
                'qanda.ecmt.permit-usage.three-options.option.cross-trade-only.label',
            EcmtPermitUsageRefDataSource::HINT_KEY =>
                'qanda.ecmt.permit-usage.three-options.option.cross-trade-only.hint',
        ],
        RefData::ECMT_PERMIT_USAGE_THREE_TRANSIT_ONLY => [
            EcmtPermitUsageRefDataSource::LABEL_KEY =>
                'qanda.ecmt.permit-usage.three-options.option.transit-only.label',
            EcmtPermitUsageRefDataSource::HINT_KEY =>
                'qanda.ecmt.permit-usage.three-options.option.transit-only.hint',
        ],
    ];
}
