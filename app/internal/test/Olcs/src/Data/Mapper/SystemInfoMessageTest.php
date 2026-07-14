<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\SystemInfoMessage;
use Laminas\Form\FormInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Data\Mapper\SystemInfoMessage::class)]
final class SystemInfoMessageTest extends MockeryTestCase
{
    public const int ID = 9999;

    public function testMapFromResult(): void
    {
        $data = [
            'id' => self::ID,
            'unit_Fld' => 'unit_Val',
        ];

        $this->assertEquals([
            SystemInfoMessage::DETAILS => [
                'id' => self::ID,
                'unit_Fld' => 'unit_Val',
            ],
        ], SystemInfoMessage::mapFromResult($data));
    }

    public function testMapFromForm(): void
    {
        $data = [
            SystemInfoMessage::DETAILS => [
                'id' => self::ID,
                'unit_Fld' => 'unit_Val',
            ],
        ];

        $this->assertEquals([
            'id' => self::ID,
            'unit_Fld' => 'unit_Val',
        ], SystemInfoMessage::mapFromForm($data));
    }

    public function testMapFromError(): void
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

        $this->assertEquals($errors, SystemInfoMessage::mapFromErrors($mockForm, $errors));
    }
}
