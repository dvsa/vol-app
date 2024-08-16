<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options;

use Dvsa\Olcs\Api\Entity\System\RefData;

class EcmtPermitUsageFourOptionsRefDataSourceFactory extends AbstractEcmtPermitUsageRefDataSourceFactory
{
    public const TRANSFORMATIONS = [
        RefData::ECMT_PERMIT_USAGE_FOUR_CROSS_TRADE_ONLY => [
            EcmtPermitUsageRefDataSource::LABEL_KEY =>
                'qanda.ecmt.permit-usage.four-options.option.cross-trade-only.label',
            EcmtPermitUsageRefDataSource::HINT_KEY =>
                'qanda.ecmt.permit-usage.four-options.option.cross-trade-only.hint',
        ],
        RefData::ECMT_PERMIT_USAGE_FOUR_NON_EU_ONLY => [
            EcmtPermitUsageRefDataSource::LABEL_KEY =>
                'qanda.ecmt.permit-usage.four-options.option.non-eu-only.label',
            EcmtPermitUsageRefDataSource::HINT_KEY =>
                'qanda.ecmt.permit-usage.four-options.option.non-eu-only.hint',
        ],
        RefData::ECMT_PERMIT_USAGE_FOUR_ECMT_WITHOUT => [
            EcmtPermitUsageRefDataSource::LABEL_KEY =>
                'qanda.ecmt.permit-usage.four-options.option.ecmt-without.label',
            EcmtPermitUsageRefDataSource::HINT_KEY =>
                'qanda.ecmt.permit-usage.four-options.option.ecmt-without.hint',
        ],
        RefData::ECMT_PERMIT_USAGE_FOUR_ALL_JOURNEYS => [
            EcmtPermitUsageRefDataSource::LABEL_KEY =>
                'qanda.ecmt.permit-usage.four-options.option.all-journeys.label',
        ],
    ];
}
