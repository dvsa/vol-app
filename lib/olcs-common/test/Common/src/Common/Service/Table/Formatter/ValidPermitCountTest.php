<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\ValidPermitCount;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Valid permit count test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class ValidPermitCountTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpFormat')]
    public function testFormat($irhpPermitTypeId, $expectedValidPermitCount): void
    {
        $row = [
            'typeId' => $irhpPermitTypeId,
            'validPermitCount' => 7
        ];

        $this->assertEquals(
            $expectedValidPermitCount,
            new ValidPermitCount()->format($row)
        );
    }

    /**
     * @return \Iterator<(int | string), array<int>>
     *
     * @psalm-return list{list{1, 7}, list{2, 7}, list{3, 7}, list{4, 7}, list{5, 7}, list{6, 1}, list{7, 1}}
     */
    public static function dpFormat(): \Iterator
    {
        yield [RefData::ECMT_PERMIT_TYPE_ID, 7];
        yield [RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID, 7];
        yield [RefData::ECMT_REMOVAL_PERMIT_TYPE_ID, 7];
        yield [RefData::IRHP_BILATERAL_PERMIT_TYPE_ID, 7];
        yield [RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID, 7];
        yield [RefData::CERT_ROADWORTHINESS_VEHICLE_PERMIT_TYPE_ID, 1];
        yield [RefData::CERT_ROADWORTHINESS_TRAILER_PERMIT_TYPE_ID, 1];
    }
}
