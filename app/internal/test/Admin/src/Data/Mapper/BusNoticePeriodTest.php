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
class BusNoticePeriodTest extends MockeryTestCase
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

        $this->assertEquals($expected, BusNoticePeriod::mapFromForm($input));
    }

    public function testMapFromResult(): void
    {
        $inputData = ['data'];
        $expected = ['busNoticePeriod' => $inputData];
        $this->assertEquals($expected, BusNoticePeriod::mapFromResult($inputData));
    }

    public function testMapFromErrors(): void
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, BusNoticePeriod::mapFromErrors($mockForm, $errors));
    }
}
