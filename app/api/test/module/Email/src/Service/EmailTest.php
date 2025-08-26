<?php

namespace Dvsa\OlcsTest\Email\Service;

use Dvsa\Olcs\Email\Service\Email;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Email\Exception\EmailNotSentException;
use Olcs\Logging\Log\Logger;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mailer\Envelope;

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

    public function testCreateServiceMissingConfig()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No mail config found');

        $config = [];

        $sm = m::mock(ContainerInterface::class);
        $sm->shouldReceive('get')->with('config')->andReturn($config);

        $this->sut->__invoke($sm, Email::class);
    }

    /**
     * Tests create service
     */
    public function testCreateService()
    {
        $config = [
            'mail' => []
        ];

        $sm = m::mock(ContainerInterface::class);
        $sm->allows('get')->with('config')->andReturns($config);

        $service = $this->sut->__invoke($sm, Email::class);

        $this->assertSame($this->sut, $service);

        $transport = $this->sut->getMailer();

        $this->assertInstanceOf(MailerInterface::class, $transport);
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
    public function testSendWithoutAttachments()
    {
        $transport = m::mock(TransportInterface::class);

        $this->sut->setMailTransport($transport);

        $transport->shouldReceive('send')
            ->once()
            ->with(m::type(Message::class))
            ->andReturnUsing(
                function (Message $message) {
                    //expecting 2 parts, text and html
                    $parts = $message->getBody()->getParts();
                    $this->assertCount(2, $parts);

                    /**
                     * @var LaminasMimePart $plainPart
                     * @var LaminasMimePart $htmlPart
                     */
                    $plainPart = $parts[0];
                    $htmlPart = $parts[1];

                    //test part one (plain text)
                    $this->assertEquals('plain content', $plainPart->getRawContent());
                    $this->assertEquals(LaminasMime::TYPE_TEXT, $plainPart->type);
                    $this->assertEquals(LaminasMime::ENCODING_QUOTEDPRINTABLE, $plainPart->encoding);
                    $this->assertInstanceOf(LaminasMimePart::class, $plainPart);

                    //test part two (html)
                    $this->assertEquals('html content', $htmlPart->getRawContent());
                    $this->assertEquals(LaminasMime::TYPE_HTML, $htmlPart->type);
                    $this->assertEquals(LaminasMime::ENCODING_QUOTEDPRINTABLE, $htmlPart->encoding);
                    $this->assertInstanceOf(LaminasMimePart::class, $htmlPart);

                    /**
                     * @var AddressList $from
                     * @var AddressList $toList
                     * @var AddressList $ccList
                     * @var AddressList $bccList
                     */
                    $headers = $message->getHeaders();
                    $from = $headers->get('from')->getAddressList();
                    $toList = $headers->get('to')->getAddressList();
                    $ccList = $headers->get('cc')->getAddressList();
                    $bccList = $headers->get('bcc')->getAddressList();

                    //test mail headers
                    $this->assertEquals(LaminasMime::MULTIPART_ALTERNATIVE, $headers->get('content-type')->getType());
                    $this->assertEquals('msg subject', $headers->get('subject')->getFieldValue());
                    $this->assertEquals(true, $from->has('foo@bar.com'));
                    $this->assertEquals(1, $from->count());
                    $this->assertEquals(true, $toList->has('bar@foo.com'));
                    $this->assertEquals(1, $toList->count());
                    $this->assertEquals(true, $ccList->has('cc1@foo.com'));
                    $this->assertEquals(true, $ccList->has('cc2@foo.com'));
                    $this->assertEquals(2, $ccList->count());
                    $this->assertEquals(true, $bccList->has('bcc1@foo.com'));
                    $this->assertEquals(true, $bccList->has('bcc2@foo.com'));
                    $this->assertEquals(true, $bccList->has('bcc3@foo.com'));
                    $this->assertEquals(3, $bccList->count());
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
    public function toFromAddressProvider()
    {
        return [
            ['foo@bar.com', 'from name', null, Email::MISSING_TO_ERROR],
            ['foo@bar.com', null, null, Email::MISSING_TO_ERROR],
            [null, 'from name', 'foo@bar.com', Email::MISSING_FROM_ERROR],
            [null, null, 'foo@bar.com', Email::MISSING_FROM_ERROR],
        ];
    }

    public function testSendHandlesException()
    {
        $this->expectException(\Dvsa\Olcs\Email\Exception\EmailNotSentException::class);
        $this->expectExceptionMessage('Email not sent: exception message');

        $transport = m::mock(TransportInterface::class);

        $this->sut->setMailTransport($transport);

        $transport->shouldReceive('send')
            ->once()
            ->with(m::type(Message::class))
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
