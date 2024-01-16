<?php

declare(strict_types=1);

namespace OlcsTest\View\Helper\SessionTimeoutWarning;

use Laminas\View\Helper\HeadMeta;
use Laminas\View\Renderer\RendererInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\View\Helper\SessionTimeoutWarning\SessionTimeoutWarning;

class SessionTimeoutWarningTest extends MockeryTestCase
{
    /**
     * Stores ini_get('session.gc_maxlifetime').
     */
    private int $sessionGcMaxLifeTime;
    private CONST secondsBeforeExpiryWarning = 60;
    private CONST timeoutRedirectUrl = 'some-url';

    public function setUp(): void
    {
        $this->sessionGcMaxLifeTime = (int) ini_get('session.gc_maxlifetime');
    }

    /**
     * @test
     */
    public function generateHeadMetaTags_ConfigSetToDisabled_RendersNothing(): void
    {
        $headMeta = m::mock(HeadMeta::class);
        $sut = $this->setupSessionTimeoutWarning($headMeta);

        $this->assertEquals('', $sut->generateHeadMetaTags());
    }

    /**
     * @test
     */
    public function generateHeadMetaTags_ConfigSetToEnabled_RendersMetaTags(): void
    {
        $indent = 999;
        $warningTimeout = $this->sessionGcMaxLifeTime - self::secondsBeforeExpiryWarning;
        $expectedResult = 'some-string';

        $headMeta = m::mock(HeadMeta::class);
        $headMeta->expects('appendName')->with(
            SessionTimeoutWarning::META_TAG_NAME_SESSION_WARNING_TIMEOUT, $warningTimeout
        );
        $headMeta->expects('appendName')->with(
            SessionTimeoutWarning::META_TAG_NAME_SESSION_REDIRECT_TIMEOUT, $this->sessionGcMaxLifeTime
        );
        $headMeta->expects('appendName')->with(
            SessionTimeoutWarning::META_TAG_NAME_TIMEOUT_REDIRECT_URL, self::timeoutRedirectUrl
        );
        $headMeta->expects('toString')->with($indent)->andReturn($expectedResult);

        $sut = $this->setupSessionTimeoutWarning($headMeta, true);

        $result = $sut->generateHeadMetaTags($indent);
        self::assertEquals($expectedResult, $result);
    }

    /**
     * Builds a SessionTimeoutWarning object with default mocks.
     *
     * @param bool $enabled
     * @param int $secondsBeforeExpiryWarning
     * @param int $secondsAppendToSessionTimeout
     * @param string $timeoutRedirectUrl
     * @return SessionTimeoutWarning
     */
    private function setupSessionTimeoutWarning(
        HeadMeta $headMeta,
        bool $enabled = false
    ): SessionTimeoutWarning
    {
        $mockedView = m::mock(RendererInterface::class);
        $headMeta->shouldReceive('setView')->with($mockedView);

        $sut = new SessionTimeoutWarning(
            $headMeta,
            $enabled,
            self::secondsBeforeExpiryWarning,
            self::timeoutRedirectUrl
        );

        $sut->setView($mockedView);

        return $sut;
    }
}
