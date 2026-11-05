<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters\SmsPhone;
use Dvsa\Olcs\Transfer\Validators\SmsPhoneNumber;
use Laminas\Validator\NotEmpty;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Form\Elements\InputFilters\SmsPhone::class)]
final class SmsPhoneTest extends MockeryTestCase
{
    public function testInit(): void
    {
        $sut = new SmsPhone();

        $sut->init();

        $this->assertSame('contact-number-sms', $sut->getLabel());
        $this->assertSame('contact-number-sms-hint', $sut->getOption('hint'));
    }

    public function testSpecIsOptionalWithSmsValidator(): void
    {
        $sut = new SmsPhone('phone_sms');

        $actual = $sut->getInputSpecification();

        $this->assertSame('phone_sms', $actual['name']);
        $this->assertFalse($actual['required']);
        $this->assertSame(
            [NotEmpty::class, SmsPhoneNumber::class],
            array_map(static fn($item) => $item['name'], $actual['validators'])
        );
        $this->assertSame(
            'contact-number-sms-required',
            $actual['validators'][0]['options']['messages'][NotEmpty::IS_EMPTY]
        );
    }
}
