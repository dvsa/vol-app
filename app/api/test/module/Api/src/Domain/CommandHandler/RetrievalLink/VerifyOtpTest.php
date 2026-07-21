<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\RetrievalLink;

use Dvsa\Olcs\Api\Domain\CommandHandler\RetrievalLink\VerifyOtp;
use Dvsa\Olcs\Api\Domain\Repository\RetrievalLink as RetrievalLinkRepo;
use Dvsa\Olcs\Api\Domain\Repository\RetrievalLinkEvent as RetrievalLinkEventRepo;
use Dvsa\Olcs\Api\Domain\Repository\RetrievalOtp as RetrievalOtpRepo;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink as RetrievalLinkEntity;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalOtp as RetrievalOtpEntity;
use Dvsa\Olcs\Api\Service\Retrieval\OtpService;
use Dvsa\Olcs\Api\Service\Retrieval\RetrievalPolicy;
use Dvsa\Olcs\Api\Service\Retrieval\SessionGrantService;
use Dvsa\Olcs\Transfer\Command\RetrievalLink\VerifyOtp as VerifyOtpCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

final class VerifyOtpTest extends AbstractCommandHandlerTestCase
{
    private OtpService $otpService;

    public function setUp(): void
    {
        $this->sut = new VerifyOtp();
        $this->otpService = new OtpService();

        $this->mockRepo('RetrievalLink', RetrievalLinkRepo::class);
        $this->mockRepo('RetrievalOtp', RetrievalOtpRepo::class);
        $this->mockRepo('RetrievalLinkEvent', RetrievalLinkEventRepo::class);

        $this->mockedSmServices = [
            OtpService::class => $this->otpService,
            SessionGrantService::class => new SessionGrantService('a-sufficiently-long-test-secret-value'),
        ];

        parent::setUp();
    }

    public function testCorrectCodeVerifiesConsumesAndIssuesGrant(): void
    {
        $this->repoMap['RetrievalLink']->shouldReceive('fetchByToken')->with('tok')->once()->andReturn($this->usableOtpLink());

        $otp = m::mock(RetrievalOtpEntity::class);
        $otp->shouldReceive('getId')->andReturn(1);
        $otp->shouldReceive('getCodeHash')->andReturn($this->otpService->hash('123456'));

        $this->repoMap['RetrievalOtp']->shouldReceive('fetchLatestActive')->once()->andReturn($otp);
        $this->repoMap['RetrievalOtp']->shouldReceive('claimAttempt')->with(1)->once()->andReturn(4);
        $this->repoMap['RetrievalOtp']->shouldReceive('consume')->with(1, m::type(\DateTime::class))->once()->andReturn(true);
        $this->repoMap['RetrievalLinkEvent']->shouldReceive('save')->once();

        $result = $this->sut->handleCommand(VerifyOtpCmd::create(['token' => 'tok', 'code' => '123456', 'ip' => '1.2.3.4']));

        self::assertTrue($result->getFlag('verified'));
        self::assertNotEmpty($result->getFlag('grant'));
    }

    public function testWrongCodeFailsAndReportsAttemptsRemaining(): void
    {
        $this->repoMap['RetrievalLink']->shouldReceive('fetchByToken')->andReturn($this->usableOtpLink());

        $otp = m::mock(RetrievalOtpEntity::class);
        $otp->shouldReceive('getId')->andReturn(1);
        $otp->shouldReceive('getCodeHash')->andReturn($this->otpService->hash('123456'));

        $this->repoMap['RetrievalOtp']->shouldReceive('fetchLatestActive')->andReturn($otp);
        $this->repoMap['RetrievalOtp']->shouldReceive('claimAttempt')->with(1)->once()->andReturn(4);
        $this->repoMap['RetrievalOtp']->shouldReceive('consume')->never();
        $this->repoMap['RetrievalLinkEvent']->shouldReceive('save')->once();

        $result = $this->sut->handleCommand(VerifyOtpCmd::create(['token' => 'tok', 'code' => '999999', 'ip' => '1.2.3.4']));

        self::assertFalse($result->getFlag('verified'));
        self::assertNull($result->getFlag('grant'));
        self::assertSame(4, $result->getFlag('attemptsRemaining'));
    }

    public function testExhaustedAttemptsAreRejectedWithoutVerifying(): void
    {
        $this->repoMap['RetrievalLink']->shouldReceive('fetchByToken')->andReturn($this->usableOtpLink());

        $otp = m::mock(RetrievalOtpEntity::class);
        $otp->shouldReceive('getId')->andReturn(1);

        $this->repoMap['RetrievalOtp']->shouldReceive('fetchLatestActive')->andReturn($otp);
        // No attempt slot left → the code is never verified and cannot be consumed.
        $this->repoMap['RetrievalOtp']->shouldReceive('claimAttempt')->with(1)->once()->andReturn(-1);
        $this->repoMap['RetrievalOtp']->shouldReceive('consume')->never();
        $this->repoMap['RetrievalLinkEvent']->shouldReceive('save')->once();

        $result = $this->sut->handleCommand(VerifyOtpCmd::create(['token' => 'tok', 'code' => 'wrong!', 'ip' => '1.2.3.4']));

        self::assertFalse($result->getFlag('verified'));
        self::assertSame(0, $result->getFlag('attemptsRemaining'));
    }

    private function usableOtpLink(): m\MockInterface
    {
        $link = m::mock(RetrievalLinkEntity::class);
        $link->shouldReceive('getRevokedAt')->with(true)->andReturn(null);
        $link->shouldReceive('getExpiresAt')->with(true)->andReturn(new \DateTime('+1 day'));
        $link->shouldReceive('getGateMode')->andReturn(RetrievalPolicy::GATE_OTP);
        $link->shouldReceive('getId')->andReturn(1);
        $link->shouldReceive('getToken')->andReturn('tok');
        $link->shouldReceive('getSourceContext')->andReturn('publication:1');

        return $link;
    }
}
