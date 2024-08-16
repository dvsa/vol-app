<?php

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\SystemInfoMessage;
use Laminas\Form\FormInterface;

/**
 * @covers Olcs\Data\Mapper\SystemInfoMessage
 */
class SystemInfoMessageTest extends MockeryTestCase
{
    public const ID = 9999;

    public function testMapFromResult()
    {
        $data = [
            'id' => self::ID,
            'unit_Fld' => 'unit_Val',
        ];

        static::assertEquals(
            [
                SystemInfoMessage::DETAILS => [
                    'id' => self::ID,
                    'unit_Fld' => 'unit_Val',
                ],
            ],
            SystemInfoMessage::mapFromResult($data)
        );
    }

    public function testMapFromForm()
    {
        $data = [
            SystemInfoMessage::DETAILS => [
                'id' => self::ID,
                'unit_Fld' => 'unit_Val',
            ],
        ];

        static::assertEquals(
            [
                'id' => self::ID,
                'unit_Fld' => 'unit_Val',
            ],
            SystemInfoMessage::mapFromForm($data)
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
            SystemInfoMessage::DETAILS => $errors['messages'],
        ];

        /** @var  FormInterface $mockForm */
        $mockForm = \Mockery::mock(FormInterface::class)
            ->shouldReceive('setMessages')
            ->with($messages)
            ->once()
            ->getMock();

        static::assertEquals(
            $errors,
            SystemInfoMessage::mapFromErrors($mockForm, $errors)
        );
    }
}
