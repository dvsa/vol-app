<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Options;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\EcmtPermitUsageRefDataSource;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\Option;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionList;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\RefDataSource;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * EcmtPermitUsageRefDataSourceTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EcmtPermitUsageRefDataSourceTest extends MockeryTestCase
{
    public const OPTIONS = [
        'categoryId' => 5
    ];

    private $optionListOption1;

    private $optionListOption2;

    private $optionListOption3;

    private $optionList;

    private $refDataSource;

    private $ecmtPermitUsageRefDataSource;

    public function setUp(): void
    {
        $this->optionListOption1 = m::mock(Option::class);
        $this->optionListOption1->shouldReceive('getValue')
            ->withNoArgs()
            ->andReturn(RefData::ECMT_PERMIT_USAGE_THREE_BOTH);
        $this->optionListOption1->shouldReceive('setLabel')
            ->with('qanda.ecmt.permit-usage.option.both.label')
            ->once();

        $this->optionListOption2 = m::mock(Option::class);
        $this->optionListOption2->shouldReceive('getValue')
            ->withNoArgs()
            ->andReturn(RefData::ECMT_PERMIT_USAGE_THREE_CROSS_TRADE_ONLY);
        $this->optionListOption2->shouldReceive('setLabel')
            ->with('qanda.ecmt.permit-usage.option.cross-trade-only.label')
            ->once();
        $this->optionListOption2->shouldReceive('setHint')
            ->with('qanda.ecmt.permit-usage.option.cross-trade-only.hint')
            ->once();

        $this->optionListOption3 = m::mock(Option::class);
        $this->optionListOption3->shouldReceive('getValue')
            ->withNoArgs()
            ->andReturn(RefData::ECMT_PERMIT_USAGE_THREE_TRANSIT_ONLY);
        $this->optionListOption3->shouldReceive('setLabel')
            ->with('qanda.ecmt.permit-usage.option.transit-only.label')
            ->once();
        $this->optionListOption3->shouldReceive('setHint')
            ->with('qanda.ecmt.permit-usage.option.transit-only.hint')
            ->once();

        $this->optionList = m::mock(OptionList::class);

        $this->refDataSource = m::mock(RefDataSource::class);
        $this->refDataSource->shouldReceive('populateOptionList')
            ->with($this->optionList, self::OPTIONS)
            ->once();

        $transformations = [
            RefData::ECMT_PERMIT_USAGE_THREE_BOTH => [
                EcmtPermitUsageRefDataSource::LABEL_KEY => 'qanda.ecmt.permit-usage.option.both.label',
            ],
            RefData::ECMT_PERMIT_USAGE_THREE_CROSS_TRADE_ONLY => [
                EcmtPermitUsageRefDataSource::LABEL_KEY => 'qanda.ecmt.permit-usage.option.cross-trade-only.label',
                EcmtPermitUsageRefDataSource::HINT_KEY => 'qanda.ecmt.permit-usage.option.cross-trade-only.hint',
            ],
            RefData::ECMT_PERMIT_USAGE_THREE_TRANSIT_ONLY => [
                EcmtPermitUsageRefDataSource::LABEL_KEY => 'qanda.ecmt.permit-usage.option.transit-only.label',
                EcmtPermitUsageRefDataSource::HINT_KEY => 'qanda.ecmt.permit-usage.option.transit-only.hint',
            ],
        ];

        $this->ecmtPermitUsageRefDataSource = new EcmtPermitUsageRefDataSource($this->refDataSource, $transformations);
    }

    public function testPopulateOptionList(): void
    {
        $optionListOptions = [
            $this->optionListOption1,
            $this->optionListOption2,
            $this->optionListOption3,
        ];

        $this->optionList->shouldReceive('getOptions')
            ->withNoArgs()
            ->andReturn($optionListOptions);

        $this->ecmtPermitUsageRefDataSource->populateOptionList($this->optionList, self::OPTIONS);
    }

    public function testPopulateOptionListException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to find transformations for option value other_ref_data');

        $optionListOption4 = m::mock(Option::class);
        $optionListOption4->shouldReceive('getValue')
            ->withNoArgs()
            ->andReturn('other_ref_data');

        $optionListOptions = [
            $this->optionListOption1,
            $this->optionListOption2,
            $this->optionListOption3,
            $optionListOption4
        ];

        $this->optionList->shouldReceive('getOptions')
            ->withNoArgs()
            ->andReturn($optionListOptions);

        $this->ecmtPermitUsageRefDataSource->populateOptionList($this->optionList, self::OPTIONS);
    }
}
