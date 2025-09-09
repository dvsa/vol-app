<?php

namespace Dvsa\OlcsTest\Email\Transport;

use Aws\S3\S3Client;
use Dvsa\Olcs\Email\Transport\ArchivingMailer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class ArchivingMailerTest extends MockeryTestCase
{
    /**
     * @throws TransportExceptionInterface
     */
    public function testArchivesOnSuccessfulSend(): void
    {
        $inner = m::mock(MailerInterface::class);
        $s3    = m::mock(S3Client::class);

        $email = (new Email())
            ->from('from@example.com')
            ->to('to@example.com')
            ->subject('Hello')
            ->text('Body');

        // inner mailer is called once
        $inner->expects('send')
            ->with(m::type(Email::class), m::any());

        // S3 putObject called with expected shape
        $s3->expects('putObject')
            ->with(m::on(function (array $args) {
                $this->assertSame('my-bucket', $args['Bucket']);
                $this->assertIsString($args['Key']);
                $this->assertStringStartsWith('emails/', $args['Key']);
                $this->assertStringEndsWith('.eml', $args['Key']);
                $this->assertSame('message/rfc822', $args['ContentType']);
                $this->assertIsString($args['Body']);
                $this->assertStringContainsString('Subject: Hello', $args['Body']);
                $this->assertStringContainsString('From: from@example.com', $args['Body']);
                $this->assertStringContainsString('To: to@example.com', $args['Body']);
                return true;
            }));

        $sut = new ArchivingMailer($inner, $s3, 'my-bucket');
        $sut->send($email);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testDoesNotArchiveWhenInnerThrows(): void
    {
        $inner = m::mock(MailerInterface::class);
        $s3    = m::mock(S3Client::class);

        $email = (new Email())->from('a@b.com')->to('c@d.com')->subject('X')->text('Y');

        $inner->expects('send')->andThrow(new TransportException('boom'));
        $s3->allows('putObject')->never();

        $sut = new ArchivingMailer($inner, $s3, 'bucket');

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('boom');

        $sut->send($email);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testPassesThroughEnvelope(): void
    {
        $inner = m::mock(MailerInterface::class);
        $s3    = m::mock(S3Client::class);

        $email = (new Email())->from('a@b.com')->to('c@d.com')->subject('S')->text('T');
        $env   = new Envelope(new Address('bounce@ex.com'), [new Address('rcpt@ex.com')]);

        $inner->expects('send')->with(m::type(Email::class), $env);
        $s3->expects('putObject')->with(m::on(fn ($args) => isset($args['Body'])));

        $sut = new ArchivingMailer($inner, $s3, 'bucket');
        $sut->send($email, $env);
    }
}
