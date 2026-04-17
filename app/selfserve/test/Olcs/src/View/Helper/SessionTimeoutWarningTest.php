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
    private const int SECONDS_BEFORE_TIMEOUT_WARNING = 60;
    private const string TIMEOUT_REDIRECT_URL = 'some-url';

    public function setUp(): void
    {
        $this->sessionGcMaxLifeTime = (int) ini_get('session.gc_maxlifetime');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function generateHeadMetaTagsConfigSetToDisabledRendersNothing(): void
    {
        $headMeta = m::mock(HeadMeta::class);
        $sut = $this->setupSessionTimeoutWarning($headMeta);

        $this->assertEquals('', $sut->generateHeadMetaTags());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function generateHeadMetaTagsConfigSetToEnabledRendersMetaTags(): void
    {
        $indent = 999;
        $warningTimeout = $this->sessionGcMaxLifeTime - self::SECONDS_BEFORE_TIMEOUT_WARNING;
        $expectedResult = 'some-string';

        $headMeta = m::mock(HeadMeta::class);
        $headMeta->expects('appendName')->with(
            SessionTimeoutWarning::META_TAG_NAME_SESSION_WARNING_TIMEOUT,
            $warningTimeout
        );
        $headMeta->expects('appendName')->with(
            SessionTimeoutWarning::META_TAG_NAME_SESSION_REDIRECT_TIMEOUT,
            $this->sessionGcMaxLifeTime
        );
        $headMeta->expects('appendName')->with(
            SessionTimeoutWarning::META_TAG_NAME_TIMEOUT_REDIRECT_URL,
            self::TIMEOUT_REDIRECT_URL
        );
        $headMeta->expects('toString')->with($indent)->andReturn($expectedResult);

        $sut = $this->setupSessionTimeoutWarning($headMeta, true);

        $result = $sut->generateHeadMetaTags($indent);
        self::assertEquals($expectedResult, $result);
    }

    /**
     * Builds a SessionTimeoutWarning object with default mocks.
     *
     * @param int $SECONDS_BEFORE_TIMEOUT_WARNING
     * @param int $secondsAppendToSessionTimeout
     * @param string $TIMEOUT_REDIRECT_URL
     * @return SessionTimeoutWarning
     */
    private function setupSessionTimeoutWarning(
        HeadMeta $headMeta,
        bool $enabled = false
    ): SessionTimeoutWarning {
        $mockedView = m::mock(RendererInterface::class);
        $headMeta->shouldReceive('setView')->with($mockedView);

        $sut = new SessionTimeoutWarning(
            $headMeta,
            $enabled,
            self::SECONDS_BEFORE_TIMEOUT_WARNING,
            self::TIMEOUT_REDIRECT_URL
        );

        $sut->setView($mockedView);

        return $sut;
    }
}
