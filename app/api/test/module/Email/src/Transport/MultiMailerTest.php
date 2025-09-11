<?php

namespace Dvsa\OlcsTest\Email\Transport;

use Dvsa\Olcs\Email\Transport\MultiMailer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MultiMailerTest extends MockeryTestCase
{
    /**
     * @throws TransportExceptionInterface
     */
    public function testCallsAllWhenAllSucceed(): void
    {
        $m1 = m::mock(MailerInterface::class);
        $m2 = m::mock(MailerInterface::class);

        $email = (new Email())->from('a@b.com')->to('c@d.com')->subject('S')->text('T');

        $m1->expects('send')->with(m::type(Email::class), m::any());
        $m2->expects('send')->with(m::type(Email::class), m::any());

        $sut = new MultiMailer([$m1, $m2]);
        $sut->send($email);

        $this->assertTrue(true);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testContinuesOnErrorAndRethrowsFirst(): void
    {
        $m1 = m::mock(MailerInterface::class);
        $m2 = m::mock(MailerInterface::class);

        $email = (new Email())->from('a@b.com')->to('c@d.com')->subject('S')->text('T');

        $m1->expects('send')->andThrow(new TransportException('first'));
        $m2->expects('send')->with(m::type(Email::class), m::any());

        $sut = new MultiMailer([$m1, $m2]);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('first');

        $sut->send($email);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRethrowsFirstWhenMultipleThrow(): void
    {
        $m1 = m::mock(MailerInterface::class);
        $m2 = m::mock(MailerInterface::class);

        $email = (new Email())->from('a@b.com')->to('c@d.com')->subject('S')->text('T');

        $m1->expects('send')->andThrow(new TransportException('first'));
        $m2->expects('send')->andThrow(new TransportException('second'));

        $sut = new MultiMailer([$m1, $m2]);

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('first');

        $sut->send($email);
    }
}
