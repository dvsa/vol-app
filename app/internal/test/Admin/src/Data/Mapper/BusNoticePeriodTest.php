<?php

declare(strict_types=1);

namespace AdminTest\Data\Mapper;

use Mockery as m;
use Admin\Data\Mapper\BusNoticePeriod;
use Laminas\Form\FormInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see BusNoticePeriod
 */
final class BusNoticePeriodTest extends MockeryTestCase
{
    public function testMapFromForm(): void
    {
        $noticeArea = 'notice area';
        $standardPeriod = 'standard period';

        $input = [
            'busNoticePeriod' => [
                'noticeArea' => $noticeArea,
                'standardPeriod' => $standardPeriod,
            ],
        ];

        $expected = [
            'noticeArea' => $noticeArea,
            'standardPeriod' => $standardPeriod,
        ];

        $this->assertSame($expected, BusNoticePeriod::mapFromForm($input));
    }

    public function testMapFromResult(): void
    {
        $inputData = ['data'];
        $expected = ['busNoticePeriod' => $inputData];
        $this->assertSame($expected, BusNoticePeriod::mapFromResult($inputData));
    }

    public function testMapFromErrors(): void
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertSame($errors, BusNoticePeriod::mapFromErrors($mockForm, $errors));
    }
}
