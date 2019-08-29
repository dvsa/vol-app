<?php

namespace PermitsTest\Data\Mapper;

use Common\Service\Helper\TranslationHelperService;
use Permits\Data\Mapper\EcmtNoOfPermits;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;

/**
 * EcmtNoOfPermitsTest
 */
class EcmtNoOfPermitsTest extends TestCase
{
    const EURO_5_CATEGORY_NAME = 'Euro 5';

    const EURO_6_CATEGORY_NAME = 'Euro 6';

    private $translationHelperService;

    private $ecmtNoOfPermits;

    public function setUp()
    {
        $this->translationHelperService = m::mock(TranslationHelperService::class);
        $this->translationHelperService->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro5')
            ->andReturn(self::EURO_5_CATEGORY_NAME);
        $this->translationHelperService->shouldReceive('translate')
            ->with('permits.page.fee.emissions.category.euro6')
            ->andReturn(self::EURO_6_CATEGORY_NAME);

        $this->ecmtNoOfPermits = new EcmtNoOfPermits(
            $this->translationHelperService
        );
    }

    public function testMapForDisplayZeroEuro5OneEuro6()
    {
        $this->translationHelperService->shouldReceive('translateReplace')
            ->with('permits.page.fee.number.permits.line.single', [1, self::EURO_6_CATEGORY_NAME])
            ->andReturn('1 permit for Euro 6');

        $data = [
            'requiredEuro5' => 0,
            'requiredEuro6' => 1
        ];

        $expectedLines = [
            '1 permit for Euro 6'
        ];

        $this->assertEquals(
            $expectedLines,
            $this->ecmtNoOfPermits->mapForDisplay($data)
        );
    }

    public function testMapForDisplayOneEuro5ZeroEuro6()
    {
        $this->translationHelperService->shouldReceive('translateReplace')
            ->with('permits.page.fee.number.permits.line.single', [1, self::EURO_5_CATEGORY_NAME])
            ->andReturn('1 permit for Euro 5');

        $data = [
            'requiredEuro5' => 1,
            'requiredEuro6' => 0
        ];

        $expectedLines = [
            '1 permit for Euro 5'
        ];

        $this->assertEquals(
            $expectedLines,
            $this->ecmtNoOfPermits->mapForDisplay($data)
        );
    }

    public function testMapForDisplayOneEuro5OneEuro6()
    {
        $this->translationHelperService->shouldReceive('translateReplace')
            ->with('permits.page.fee.number.permits.line.single', [1, self::EURO_5_CATEGORY_NAME])
            ->andReturn('1 permit for Euro 5');

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with('permits.page.fee.number.permits.line.single', [1, self::EURO_6_CATEGORY_NAME])
            ->andReturn('1 permit for Euro 6');

        $data = [
            'requiredEuro5' => 1,
            'requiredEuro6' => 1
        ];

        $expectedLines = [
            '1 permit for Euro 5',
            '1 permit for Euro 6'
        ];

        $this->assertEquals(
            $expectedLines,
            $this->ecmtNoOfPermits->mapForDisplay($data)
        );
    }

    public function testMapForDisplayOneEuro5MultipleEuro6()
    {
        $this->translationHelperService->shouldReceive('translateReplace')
            ->with('permits.page.fee.number.permits.line.single', [1, self::EURO_5_CATEGORY_NAME])
            ->andReturn('1 permit for Euro 5');

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with('permits.page.fee.number.permits.line.multiple', [2, self::EURO_6_CATEGORY_NAME])
            ->andReturn('2 permits for Euro 6');

        $data = [
            'requiredEuro5' => 1,
            'requiredEuro6' => 2
        ];

        $expectedLines = [
            '1 permit for Euro 5',
            '2 permits for Euro 6'
        ];

        $this->assertEquals(
            $expectedLines,
            $this->ecmtNoOfPermits->mapForDisplay($data)
        );
    }

    public function testMapForDisplayMultipleEuro5OneEuro6()
    {
        $this->translationHelperService->shouldReceive('translateReplace')
            ->with('permits.page.fee.number.permits.line.multiple', [2, self::EURO_5_CATEGORY_NAME])
            ->andReturn('2 permits for Euro 5');

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with('permits.page.fee.number.permits.line.single', [1, self::EURO_6_CATEGORY_NAME])
            ->andReturn('1 permit for Euro 6');

        $data = [
            'requiredEuro5' => 2,
            'requiredEuro6' => 1
        ];

        $expectedLines = [
            '2 permits for Euro 5',
            '1 permit for Euro 6'
        ];

        $this->assertEquals(
            $expectedLines,
            $this->ecmtNoOfPermits->mapForDisplay($data)
        );
    }

    public function testMapForDisplayMultipleEuro5MultipleEuro6()
    {
        $this->translationHelperService->shouldReceive('translateReplace')
            ->with('permits.page.fee.number.permits.line.multiple', [2, self::EURO_5_CATEGORY_NAME])
            ->andReturn('2 permits for Euro 5');

        $this->translationHelperService->shouldReceive('translateReplace')
            ->with('permits.page.fee.number.permits.line.multiple', [2, self::EURO_6_CATEGORY_NAME])
            ->andReturn('2 permits for Euro 6');

        $data = [
            'requiredEuro5' => 2,
            'requiredEuro6' => 2
        ];

        $expectedLines = [
            '2 permits for Euro 5',
            '2 permits for Euro 6'
        ];

        $this->assertEquals(
            $expectedLines,
            $this->ecmtNoOfPermits->mapForDisplay($data)
        );
    }
}
