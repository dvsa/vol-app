<?php

namespace Permits\Data\Mapper;

/**
 * @todo clearly this will need to be a lot better later - but will wait to see first if it's staying
 *
 * Fee list mapper
 */
class FeeList
{
    public static function mapForDisplay (array $data) {
        return [
            'appFee' => $data['fee']['IRHP_GV_APP_ECMT']['fixedValue'],
            'issueFee' => $data['fee']['IRHP_GV_ECMT_100_PERMIT_FEE']['fixedValue'],
        ];
    }
}
