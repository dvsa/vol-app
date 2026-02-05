<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Queue\Service\Message;

use Dvsa\Olcs\Queue\Service\Message\CompaniesHouse\CompanyProfile;
use Dvsa\Olcs\Queue\Service\Message\CompaniesHouse\ProcessInsolvency;
use Dvsa\Olcs\Queue\Service\Message\MessageBuilder;
use PHPUnit\Framework\TestCase;

class MessageBuilderTest extends TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new MessageBuilder();
    }

    public function testBuildMessages(): void
    {
        $messages = $this->sut->buildMessages(
            [
                ['companyOrLlpNo' => 123],
                ['companyOrLlpNo' => 456]
            ],
            CompanyProfile::class,
            ['CompanyProfile_URL' => 'some_url']
        );

        $expected = [
            new CompanyProfile(['companyOrLlpNo' => 123], 'some_url'),
            new CompanyProfile(['companyOrLlpNo' => 456], 'some_url')
        ];

        $this->assertEquals($expected, $messages);
    }

    public function testBuildMessage(): void
    {
        $messages = $this->sut->buildMessage(
            ['companyOrLlpNo' => 123],
            ProcessInsolvency::class,
            ['ProcessInsolvency_URL' => 'some_url']
        );

        $expected = new ProcessInsolvency(['companyOrLlpNo' => 123], 'some_url');

        $this->assertEquals($expected, $messages);
    }

    public function testExceptionThrownIfInvalidMessageType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The NonExistentClass class does not exist');

        $this->sut->buildMessages(
            [
                ['companyOrLlpNo' => 123],
            ],
            'NonExistentClass',
            ['NonExistentClass_URL' => 'some_url']
        );
    }

    public function testExceptionThrownIfQueueConfigDoesNotExist(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The url config CompanyProfile_URL does not exist');

        $this->sut->buildMessages(
            [
                ['companyOrLlpNo' => 123],
            ],
            CompanyProfile::class,
            ['Wrong_URL_key' => 'some_url']
        );
    }
}
