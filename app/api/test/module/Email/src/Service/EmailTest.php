<?php

namespace Dvsa\OlcsTest\Email\Service;

use Dvsa\Olcs\Email\Exception\EmailNotSentException;
use Dvsa\Olcs\Email\Service\Email;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;


class EmailTest extends MockeryTestCase
{
    /**
     * @var Email
     */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new Email();

        $logWriter = new \Laminas\Log\Writer\Mock();
        $logger = new \Laminas\Log\Logger();
        $logger->addWriter($logWriter);

        Logger::setLogger($logger);
    }

    public function testCreateServiceMissingConfig(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No mail config found');

        $config = [];

        $sm = m::mock(ContainerInterface::class);
        $sm->expects('get')
            ->with('config')
            ->andReturns($config);

        $this->sut->__invoke($sm, Email::class);
    }

    public function testCreateServiceBuildsMailer(): void
    {
        $config = [
            'mail' => [
                'type'    => '\Laminas\Mail\Transport\Smtp',
                'options' => [
                    'host' => 'localhost',
                    'port' => 1025,
                ],
            ],
        ];

        $sm = m::mock(ContainerInterface::class);
        $sm->allows()->get('config')->andReturns($config);

        $service = $this->sut->__invoke($sm, Email::class);

        $this->assertSame($this->sut, $service);

        $mailer = $this->sut->getMailer();
        $this->assertInstanceOf(\Symfony\Component\Mailer\MailerInterface::class, $mailer);
    }

    /**
     * Tests sending plain text email
     */
    public function testSendText()
    {
        $transport = m::mock(MailerInterface::class);
        $this->sut->setMailer($transport);

        $transport->expects('send')
            ->withArgs(function ($message, $envelope = null) {
                // 1) Right types
                $this->assertInstanceOf(SymfonyEmail::class, $message);
                // Envelope is optional; we don’t care here
                $this->assertTrue($envelope === null || $envelope instanceof \Symfony\Component\Mailer\Envelope);

                // 2) Check interesting bits on the Email
                $raw = $message->toString();
                $this->assertStringContainsString("From: foo@bar.com", $raw);
                $this->assertStringContainsString("To: bar@foo.com", $raw);
                $this->assertStringContainsString("Cc: cc@foo.com", $raw);

                // Bcc often isn’t in the serialized raw headers sent over the wire,
                // but it *is* on the Email headers object:
                $bcc = $message->getHeaders()->get('Bcc');
                $this->assertNotNull($bcc);
                $this->assertStringContainsString('bcc@foo.com', $bcc->getBodyAsString());

                $this->assertStringContainsString("Subject: Subject", $raw);
                $this->assertMatchesRegularExpression('/^Content-Type:\s*text\/plain;\s*charset=utf-8/im', $raw);
                $this->assertMatchesRegularExpression('/^Content-Transfer-Encoding:\s*quoted-printable/im', $raw);
                $this->assertStringContainsString("\r\n\r\nThis is the content", $raw);

                return true; // tell Mockery the expectation matched
            });

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
     * @throws EmailNotSentException
     */
    public function testSendWithAttachments(): void
    {
        $transport = m::mock(MailerInterface::class);
        $this->sut->setMailer($transport);

        $transport->expects('send')
            ->withArgs(function ($message, $envelope = null) {
                // Types
                $this->assertInstanceOf(SymfonyEmail::class, $message);
                $this->assertTrue($envelope === null || $envelope instanceof Envelope);

                // Subject
                $this->assertSame('msg subject', $message->getSubject());

                // From / To / Cc / Bcc
                $this->assertSame(['foo@bar.com'], array_map(fn($a) => $a->getAddress(), $message->getFrom()));
                $this->assertSame(['bar@foo.com'], array_map(fn($a) => $a->getAddress(), $message->getTo()));
                $this->assertSame(['cc1@foo.com','cc2@foo.com'], array_map(fn($a) => $a->getAddress(), $message->getCc()));
                $this->assertSame(['bcc1@foo.com','bcc2@foo.com','bcc3@foo.com'], array_map(fn($a) => $a->getAddress(), $message->getBcc()));

                // Bodies
                $this->assertSame('plain content', $message->getTextBody());
                $this->assertSame('html content',  $message->getHtmlBody());

                // Attachments: two present
                $attachments = $message->getAttachments();
                $this->assertCount(2, $attachments);

                // Filenames (prefer API; fallback to raw string check if API differs)
                $names = [];
                foreach ($attachments as $att) {
                    if (method_exists($att, 'getFilename')) {
                        $names[] = $att->getFilename();
                    }
                }
                if ($names) {
                    sort($names);
                    $this->assertSame(['docFilename','docFilename2'], $names);
                } else {
                    $raw = $message->toString();
                    $this->assertStringContainsString('filename=docFilename',  $raw);
                    $this->assertStringContainsString('filename=docFilename2', $raw);
                }

                // MIME structure sanity: multipart/mixed with alternative inside (symfony builds this automatically)
                $raw = $message->toString();
                $this->assertMatchesRegularExpression('/^MIME-Version:\s*1\.0/im', $raw);
                $this->assertMatchesRegularExpression('/^Content-Type:\s*multipart\/mixed;/im', $raw);
                $this->assertStringContainsString("Content-Type: text/plain; charset=utf-8", $raw);
                $this->assertStringContainsString("Content-Type: text/html; charset=utf-8", $raw);

                return true; // tell Mockery the args matched
            });

        $docs = [
            ['content' => 'docContent',  'fileName' => 'docFilename'],
            ['content' => 'docContent2', 'fileName' => 'docFilename2'],
        ];

        $cc  = ['invalid-email', 'cc1@foo.com', 'cc2@foo.com'];
        $bcc = ['bcc1@foo.com', 'bcc2@foo.com', 'bcc3@foo.com'];

        $this->sut->send(
            'foo@bar.com',     // from
            'foo',             // fromName
            'bar@foo.com',     // to
            'msg subject',     // subject
            'plain content',   // plain body
            'html content',    // html body
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
        $transport = m::mock(MailerInterface::class);
        $this->sut->setMailer($transport);

        $transport->expects('send')
            ->withArgs(function ($message, $envelope = null) {
                // Types
                $this->assertInstanceOf(SymfonyEmail::class, $message);
                $this->assertTrue($envelope === null || $envelope instanceof Envelope);

                // Recipients / subject
                $this->assertSame(['foo@bar.com'], array_map(fn($a) => $a->getAddress(), $message->getFrom()));
                $this->assertSame(['bar@foo.com'], array_map(fn($a) => $a->getAddress(), $message->getTo()));
                $this->assertSame(['cc1@foo.com','cc2@foo.com'], array_map(fn($a) => $a->getAddress(), $message->getCc()));
                $this->assertSame(['bcc1@foo.com','bcc2@foo.com','bcc3@foo.com'], array_map(fn($a) => $a->getAddress(), $message->getBcc()));
                $this->assertSame('msg subject', $message->getSubject());

                // Bodies
                $this->assertSame('plain content', $message->getTextBody());
                $this->assertSame('html content',  $message->getHtmlBody());

                // No attachments
                $this->assertCount(0, $message->getAttachments());

                // MIME structure (order can vary; assert presence)
                $raw = $message->toString();
                $this->assertMatchesRegularExpression('/^MIME-Version:\s*1\.0/im', $raw);
                $this->assertMatchesRegularExpression('/^Content-Type:\s*multipart\/alternative;/im', $raw);
                $this->assertStringContainsString('Content-Type: text/plain; charset=utf-8', $raw);
                $this->assertStringContainsString('Content-Type: text/html; charset=utf-8',  $raw);

                return true; // tell Mockery the args matched
            });

        $cc  = ['cc1@foo.com', 'cc2@foo.com', 'invalid-email']; // invalid ignored by buildAddresses()
        $bcc = ['bcc1@foo.com', 'bcc2@foo.com', 'bcc3@foo.com', null];

        $this->sut->send(
            'foo@bar.com',     // from
            'foo',             // fromName
            'bar@foo.com',     // to
            'msg subject',     // subject
            'plain content',   // text
            'html content',    // html
            $cc,
            $bcc,
            []                 // no attachments
        );
    }

    /**
     * Tests sending an email without attachments
     *
     * @dataProvider toFromAddressProvider
     */
    public function testToFromAddressException($fromEmail, $fromName, $toEmail, $exceptionMessage)
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
    public function toFromAddressProvider(): array
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

        $transport = m::mock(MailerInterface::class);
        $this->sut->setMailer($transport);

        $transport->expects('send')
            // Symfony's signature is send(RawMessage $message, ?Envelope $envelope = null)
            ->withArgs(function ($message, $envelope = null) {
                $this->assertInstanceOf(SymfonyEmail::class, $message);
                // envelope can be null; we don't care here
                return true;
            })
            ->andThrow(new TransportException('exception message'));

        $this->sut->send(
            'foo@bar.com',
            'foo',
            'bar@foo.com',
            'Subject',
            'This is the content',
            null // html body
        );
    }
}
