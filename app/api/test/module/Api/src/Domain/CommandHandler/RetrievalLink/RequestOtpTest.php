<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\RetrievalLink;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\RetrievalLink\RequestOtp;
use Dvsa\Olcs\Api\Domain\Repository\RetrievalLink as RetrievalLinkRepo;
use Dvsa\Olcs\Api\Domain\Repository\RetrievalLinkEvent as RetrievalLinkEventRepo;
use Dvsa\Olcs\Api\Domain\Repository\RetrievalOtp as RetrievalOtpRepo;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink as RetrievalLinkEntity;
use Dvsa\Olcs\Api\Service\Retrieval\OtpService;
use Dvsa\Olcs\Api\Service\Retrieval\RetrievalPolicy;
use Dvsa\Olcs\Email\Domain\Command\SendEmail;
use Dvsa\Olcs\Email\Service\TemplateRenderer;
use Dvsa\Olcs\Transfer\Command\RetrievalLink\RequestOtp as RequestOtpCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

final class RequestOtpTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RequestOtp();

        $this->mockRepo('RetrievalLink', RetrievalLinkRepo::class);
        $this->mockRepo('RetrievalOtp', RetrievalOtpRepo::class);
        $this->mockRepo('RetrievalLinkEvent', RetrievalLinkEventRepo::class);

        $this->mockedSmServices = [
            OtpService::class => new OtpService(),
            TemplateRenderer::class => m::mock(TemplateRenderer::class),
        ];

        parent::setUp();
    }

    public function testIssuesAndEmailsCodeForOtpLinkWithinRateLimit(): void
    {
        $link = m::mock(RetrievalLinkEntity::class);
        $link->shouldReceive('getRevokedAt')->with(true)->andReturn(null);
        $link->shouldReceive('getExpiresAt')->with(true)->andReturn(new \DateTime('+1 day'));
        $link->shouldReceive('getGateMode')->andReturn(RetrievalPolicy::GATE_OTP);
        $link->shouldReceive('getId')->andReturn(1);
        $link->shouldReceive('getRecipientEmail')->andReturn('team@police.example');
        $link->shouldReceive('getSourceContext')->andReturn('publication:1');

        $this->repoMap['RetrievalLink']->shouldReceive('fetchByToken')->andReturn($link);
        $this->repoMap['RetrievalOtp']->shouldReceive('countRequestsSince')->once()->andReturn(0);
        $this->repoMap['RetrievalOtp']->shouldReceive('invalidateActiveForLink')->once();
        $this->repoMap['RetrievalOtp']->shouldReceive('save')->once();
        $this->repoMap['RetrievalLinkEvent']->shouldReceive('save')->once();
        $this->mockedSmServices[TemplateRenderer::class]->shouldReceive('renderBody')->once();

        $this->expectedSideEffect(SendEmail::class, ['to' => 'team@police.example'], new Result());

        $result = $this->sut->handleCommand(RequestOtpCmd::create(['token' => 'tok', 'ip' => '1.2.3.4']));

        self::assertNotEmpty($result->getMessages());
    }

    public function testRateLimitedRequestSendsNothing(): void
    {
        $link = m::mock(RetrievalLinkEntity::class);
        $link->shouldReceive('getRevokedAt')->with(true)->andReturn(null);
        $link->shouldReceive('getExpiresAt')->with(true)->andReturn(new \DateTime('+1 day'));
        $link->shouldReceive('getGateMode')->andReturn(RetrievalPolicy::GATE_OTP);
        $link->shouldReceive('getId')->andReturn(1);
        $link->shouldReceive('getSourceContext')->andReturn('publication:1');

        $this->repoMap['RetrievalLink']->shouldReceive('fetchByToken')->andReturn($link);
        $this->repoMap['RetrievalOtp']->shouldReceive('countRequestsSince')->once()->andReturn(5);
        $this->repoMap['RetrievalOtp']->shouldReceive('save')->never();
        // A 'denied' event is still recorded, but no OTP is stored and no email is sent.
        $this->repoMap['RetrievalLinkEvent']->shouldReceive('save')->once();

        $result = $this->sut->handleCommand(RequestOtpCmd::create(['token' => 'tok', 'ip' => '1.2.3.4']));

        self::assertNotEmpty($result->getMessages());
    }

    public function testNonOtpLinkIsANoOp(): void
    {
        $link = m::mock(RetrievalLinkEntity::class);
        $link->shouldReceive('getRevokedAt')->with(true)->andReturn(null);
        $link->shouldReceive('getExpiresAt')->with(true)->andReturn(new \DateTime('+1 day'));
        $link->shouldReceive('getGateMode')->andReturn(RetrievalPolicy::GATE_NONE);

        $this->repoMap['RetrievalLink']->shouldReceive('fetchByToken')->andReturn($link);
        $this->repoMap['RetrievalOtp']->shouldReceive('save')->never();

        // Same generic message whether or not a code was sent — no existence oracle.
        $result = $this->sut->handleCommand(RequestOtpCmd::create(['token' => 'tok', 'ip' => '1.2.3.4']));

        self::assertNotEmpty($result->getMessages());
    }
}
