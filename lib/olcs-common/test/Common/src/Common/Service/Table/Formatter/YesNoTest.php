<?php

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\StackHelperService;
use Common\Service\Table\Formatter\YesNo;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Common\Service\Table\Formatter\YesNo
 */
class YesNoTest extends MockeryTestCase
{
    protected $stackHelper;

    protected $translator;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->stackHelper = m::mock(StackHelperService::class);
        $this->sut = new YesNo($this->stackHelper, $this->translator);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    /**
     * Test the format method
     *
     * @group Formatters
     * @group YesNoFormatter
     *
     * @dataProvider dpTestFormatByName
     */
    public function testFormatByName($data, $column, $expected): void
    {
        $this->translator
            ->shouldReceive('translate')->once()->with('common.table.' . $expected)->andReturn('EXPECT');

        static::assertEquals('EXPECT', $this->sut->format($data, $column));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function dpTestFormatByName()
    {
        return [
            [
                'data' => ['yesorno' => 1],
                'column' => ['name' => 'yesorno'],
                'expect' => 'Yes',
            ],
            [
                'data' => ['yesorno' => 0],
                'column' => ['name' => 'yesorno'],
                'expect' => 'No',
            ],
            [
                'data' => ['yesorno' => 'Y'],
                'column' => ['name' => 'yesorno'],
                'expect' => 'Yes',
            ],
            [
                'data' => ['yesorno' => 'N'],
                'column' => ['name' => 'yesorno'],
                'expect' => 'No',
            ],
            [
                'data' => ['yesorno' => 'something'],
                'column' => ['name' => 'yesorno'],
                'expect' => 'Yes',
            ],
        ];
    }

    public function testFormatBySlack(): void
    {
        $data = ['data'];
        $column = [
            'stack' => 'fieldset->fieldset2->field',
        ];

        $this->translator->shouldReceive('translate')->once()->with('common.table.Yes')->andReturn('EXPECT');
        $this->stackHelper
                    ->shouldReceive('getStackValue')
                    ->once()
                    ->with($data, ['fieldset', 'fieldset2', 'field'])
                    ->andReturn('Y');

        static::assertEquals('EXPECT', $this->sut->format($data, $column));
    }
}
