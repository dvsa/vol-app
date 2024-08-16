<?php

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\PublicHoliday;
use Laminas\Form\FormInterface;

/**
 * @covers Olcs\Data\Mapper\PublicHoliday
 */
class PublicHolidayTest extends MockeryTestCase
{
    public const ID = 9999;

    public function testMapFromResult()
    {
        $data = [
            'id' => self::ID,
            'unit_Fld' => 'unit_Val',
            'isEngland' => 'Y',
            'isWales' => 'N',
            'isScotland' => 'XXX',
            'publicHolidayDate' => 'unit_Date',
        ];

        static::assertEquals(
            [
                PublicHoliday::FIELDS => [
                    'id' => self::ID,
                    'areas' => ['isEngland'],
                    'holidayDate' => 'unit_Date',
                ],
            ],
            PublicHoliday::mapFromResult($data)
        );
    }

    public function testMapFromResultIsEmpty()
    {
        static::assertEquals(
            [
                PublicHoliday::FIELDS => [],
            ],
            PublicHoliday::mapFromResult([])
        );
    }

    public function testMapFromForm()
    {
        $data = [
            PublicHoliday::FIELDS => [
                'id' => self::ID,
                'unit_Fld' => 'unit_Val',
                'areas' => ['isEngland', 'isNi'],
                'holidayDate' => 'unit_Date',
            ],
        ];

        static::assertEquals(
            [
                'id' => self::ID,
                'unit_Fld' => 'unit_Val',
                'isEngland' => 'Y',
                'isNi' => 'Y',
                'holidayDate' => 'unit_Date',
            ],
            PublicHoliday::mapFromForm($data)
        );
    }

    public function testMapFromError()
    {
        $errors = [
            'messages' => [
                'unit_Fld' => [
                    'unit_Err' => 'unit_ErrDesc',
                ],
            ],
        ];

        $messages = [
            PublicHoliday::FIELDS => $errors['messages'],
        ];

        /** @var  FormInterface $mockForm */
        $mockForm = \Mockery::mock(FormInterface::class)
            ->shouldReceive('setMessages')
            ->with($messages)
            ->once()
            ->getMock();

        static::assertEquals(
            $errors,
            PublicHoliday::mapFromErrors($mockForm, $errors)
        );
    }
}
