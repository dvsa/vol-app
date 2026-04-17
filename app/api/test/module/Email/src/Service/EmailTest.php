<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Email\Service;

use Dvsa\Olcs\Email\Service\Email;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Email\Exception\EmailNotSentException;
use Olcs\Logging\Log\Logger;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailTest extends MockeryTestCase
{
    /**
     * @var Email
     */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new Email();

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);
    }

    public function testCreateServiceMissingConfig(): void
    {
        $this->expectException(\RuntimeException::class);

        $config = [];

        $sm = m::mock(ContainerInterface::class);
        $sm->shouldReceive('get')->with('config')->andReturn($config);

        $this->sut->__invoke($sm, Email::class);
    }

    /**
     * Tests create service
     */
    public function testCreateService(): void
    {
        $config = [
            'mail' => [
                'options' => [
                    'host' => 'localhost',
                    'port' => 25,
                    'connection_config' => [
                        'username' => null,
                        'password' => null
                    ]
                ]
            ]
        ];

        $sm = m::mock(ContainerInterface::class);
        $sm->shouldReceive('get')->with('config')->andReturn($config);

        $service = $this->sut->__invoke($sm, Email::class);

        $this->assertSame($this->sut, $service);

        $mailer = $this->sut->getMailer();

        $this->assertInstanceOf(MailerInterface::class, $mailer);
    }

    /**
     * Tests sending plain text email
     */
    public function testSendText(): void
    {
        $mailer = m::mock(MailerInterface::class);

        $this->sut->setMailer($mailer);

        $mailer->shouldReceive('send')
            ->once()
            ->with(m::type(SymfonyEmail::class))
            ->andReturnUsing(
                function (SymfonyEmail $email) {
                    // Verify from address
                    $from = $email->getFrom();
                    $this->assertCount(1, $from);
                    $this->assertEquals('foo@bar.com', $from[0]->getAddress());

                    // Verify to address
                    $to = $email->getTo();
                    $this->assertCount(1, $to);
                    $this->assertEquals('bar@foo.com', $to[0]->getAddress());

                    // Verify cc address
                    $cc = $email->getCc();
                    $this->assertCount(1, $cc);
                    $this->assertEquals('cc@foo.com', $cc[0]->getAddress());

                    // Verify bcc address
                    $bcc = $email->getBcc();
                    $this->assertCount(1, $bcc);
                    $this->assertEquals('bcc@foo.com', $bcc[0]->getAddress());

                    // Verify subject
                    $this->assertEquals('Subject', $email->getSubject());

                    // Verify body
                    $this->assertEquals('This is the content', $email->getTextBody());
                }
            );

        $this->sut->send(
            'foo@bar.com',
            'foo',
            'bar@foo.com',
            'Subject',
            'This is the content',
            null,
            ['cc@foo.com'],
            ['bcc@foo.com']
        );
    }

    /**
     * Tests sending an email with attachments
     */
    public function testSendWithAttachments(): void
    {
        $mailer = m::mock(MailerInterface::class);

        $this->sut->setMailer($mailer);

        $mailer->shouldReceive('send')
            ->once()
            ->with(m::type(SymfonyEmail::class))
            ->andReturnUsing(
                function (SymfonyEmail $email) {
                    // Verify subject
                    $this->assertEquals('msg subject', $email->getSubject());

                    // Verify from address
                    $from = $email->getFrom();
                    $this->assertCount(1, $from);
                    $this->assertEquals('foo@bar.com', $from[0]->getAddress());

                    // Verify to address
                    $to = $email->getTo();
                    $this->assertCount(1, $to);
                    $this->assertEquals('bar@foo.com', $to[0]->getAddress());

                    // Verify cc addresses (invalid-email should be filtered out)
                    $cc = $email->getCc();
                    $this->assertCount(2, $cc);
                    $ccAddresses = array_map(fn($addr) => $addr->getAddress(), $cc);
                    $this->assertContains('cc1@foo.com', $ccAddresses);
                    $this->assertContains('cc2@foo.com', $ccAddresses);

                    // Verify bcc addresses (null should be filtered out)
                    $bcc = $email->getBcc();
                    $this->assertCount(3, $bcc);
                    $bccAddresses = array_map(fn($addr) => $addr->getAddress(), $bcc);
                    $this->assertContains('bcc1@foo.com', $bccAddresses);
                    $this->assertContains('bcc2@foo.com', $bccAddresses);
                    $this->assertContains('bcc3@foo.com', $bccAddresses);

                    // Verify plain and html body
                    $this->assertEquals('plain content', $email->getTextBody());
                    $this->assertEquals('html content', $email->getHtmlBody());

                    // Verify attachments
                    $attachments = $email->getAttachments();
                    $this->assertCount(2, $attachments);

                    $this->assertEquals('docFilename', $attachments[0]->getFilename());
                    $this->assertEquals('docContent', $attachments[0]->getBody());

                    $this->assertEquals('docFilename2', $attachments[1]->getFilename());
                    $this->assertEquals('docContent2', $attachments[1]->getBody());
                }
            );

        $docs = [
            0 => [
                'content' => 'docContent',
                'fileName' => 'docFilename'
            ],
            1 => [
                'content' => 'docContent2',
                'fileName' => 'docFilename2'
            ]
        ];

        $cc = ['invalid-email', 'cc1@foo.com', 'cc2@foo.com'];
        $bcc = [null, 'bcc1@foo.com', 'bcc2@foo.com', 'bcc3@foo.com'];

        $this->sut->send(
            'foo@bar.com',
            'foo',
            'bar@foo.com',
            'msg subject',
            'plain content',
            'html content',
            $cc,
            $bcc,
            $docs
        );
    }

    /**
     * Tests sending an email without attachments
     */
    public function testSendWithoutAttachments(): void
    {
        $mailer = m::mock(MailerInterface::class);

        $this->sut->setMailer($mailer);

        $mailer->shouldReceive('send')
            ->once()
            ->with(m::type(SymfonyEmail::class))
            ->andReturnUsing(
                function (SymfonyEmail $email) {
                    // Verify subject
                    $this->assertEquals('msg subject', $email->getSubject());

                    // Verify plain and html body
                    $this->assertEquals('plain content', $email->getTextBody());
                    $this->assertEquals('html content', $email->getHtmlBody());

                    // Verify from address
                    $from = $email->getFrom();
                    $this->assertCount(1, $from);
                    $this->assertEquals('foo@bar.com', $from[0]->getAddress());

                    // Verify to address
                    $to = $email->getTo();
                    $this->assertCount(1, $to);
                    $this->assertEquals('bar@foo.com', $to[0]->getAddress());

                    // Verify cc addresses (invalid-email should be filtered out)
                    $cc = $email->getCc();
                    $this->assertCount(2, $cc);
                    $ccAddresses = array_map(fn($addr) => $addr->getAddress(), $cc);
                    $this->assertContains('cc1@foo.com', $ccAddresses);
                    $this->assertContains('cc2@foo.com', $ccAddresses);

                    // Verify bcc addresses (null should be filtered out)
                    $bcc = $email->getBcc();
                    $this->assertCount(3, $bcc);
                    $bccAddresses = array_map(fn($addr) => $addr->getAddress(), $bcc);
                    $this->assertContains('bcc1@foo.com', $bccAddresses);
                    $this->assertContains('bcc2@foo.com', $bccAddresses);
                    $this->assertContains('bcc3@foo.com', $bccAddresses);

                    // Verify no attachments
                    $attachments = $email->getAttachments();
                    $this->assertCount(0, $attachments);
                }
            );

        $cc = ['cc1@foo.com', 'cc2@foo.com', 'invalid-email'];
        $bcc = ['bcc1@foo.com', 'bcc2@foo.com', 'bcc3@foo.com', null];

        $this->sut->send(
            'foo@bar.com',
            'foo',
            'bar@foo.com',
            'msg subject',
            'plain content',
            'html content',
            $cc,
            $bcc,
            []
        );
    }

    /**
     * Tests sending an email without attachments
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('toFromAddressProvider')]
    public function testToFromAddressException(mixed $fromEmail, mixed $fromName, mixed $toEmail, mixed $exceptionMessage): void
    {
        $this->expectException(EmailNotSentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->sut->send(
            $fromEmail,
            $fromName,
            $toEmail,
            'msg subject',
            'plain content',
            'html content',
            [],
            [],
            []
        );
    }

    /**
     * @return array
     */
    public static function toFromAddressProvider(): array
    {
        return [
            ['foo@bar.com', 'from name', null, Email::MISSING_TO_ERROR],
            ['foo@bar.com', null, null, Email::MISSING_TO_ERROR],
            [null, 'from name', 'foo@bar.com', Email::MISSING_FROM_ERROR],
            [null, null, 'foo@bar.com', Email::MISSING_FROM_ERROR],
        ];
    }

    public function testSendHandlesException(): void
    {
        $this->expectException(\Dvsa\Olcs\Email\Exception\EmailNotSentException::class);
        $this->expectExceptionMessage('Email not sent: exception message');

        $mailer = m::mock(MailerInterface::class);

        $this->sut->setMailer($mailer);

        $mailer->shouldReceive('send')
            ->once()
            ->with(m::type(SymfonyEmail::class))
            ->andThrow(new \Exception('exception message'));

        $this->sut->send(
            'foo@bar.com',
            'foo',
            'bar@foo.com',
            'Subject',
            'This is the content',
            null
        );
    }
}
