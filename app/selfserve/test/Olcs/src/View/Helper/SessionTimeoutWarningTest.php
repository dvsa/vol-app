<?php

namespace OlcsTest\View\Helper\SessionTimeoutWarning;

use DOMDocument;
use DOMNode;
use Laminas\View\Renderer\RendererInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\View\Helper\SessionTimeoutWarning\SessionTimeoutWarning;

class SessionTimeoutWarningTest extends MockeryTestCase
{
    /**
     * Stores ini_get('session.gc_maxlifetime').
     * @var int
     */
    private $sessionGcMaxLifeTime;

    public function setUp(): void
    {
        $this->sessionGcMaxLifeTime = (int) ini_get('session.gc_maxlifetime');
    }

    /**
     * @test
     */
    public function generateHeadMetaTags_IsCallable()
    {
        $sut = $this->setupSessionTimeoutWarning();

        $this->assertIsCallable([$sut, 'generateHeadMetaTags']);
    }

    /**
     * @test
     * @depends generateHeadMetaTags_IsCallable
     */
    public function generateHeadMetaTags_ConfigSetToDisabled_RendersNothing()
    {
        $sut = $this->setupSessionTimeoutWarning();

        $this->assertEquals('', $sut->generateHeadMetaTags());
    }

    /**
     * @test
     * @depends generateHeadMetaTags_IsCallable
     */
    public function generateHeadMetaTags_ConfigSetToEnabled_RendersMetaTags()
    {
        $sut = $this->setupSessionTimeoutWarning(true);

        $result = $sut->generateHeadMetaTags();

        $result = explode(PHP_EOL, $result);
        $this->assertCount(3, $result);

        foreach ($result as $metaTag) {
            $this->assertMatchesRegularExpression('/^<meta.+\/>$/', $metaTag);
        }
    }

    /**
     * @test
     * @depends generateHeadMetaTags_ConfigSetToEnabled_RendersMetaTags
     * @throws \Exception
     */
    public function generateHeadMetaTags_IsDefined_SessionWarningTimeout()
    {
        $sut = $this->setupSessionTimeoutWarning(true, $secondsBeforeExpiryWarning=60);
        $metaContent = $this->getMetaTagContent($sut->generateHeadMetaTags(), SessionTimeoutWarning::META_TAG_NAME_SESSION_WARNING_TIMEOUT);

        $this->assertNotEmpty($metaContent);
    }

    /**
     * @test
     * @depends generateHeadMetaTags_IsDefined_SessionWarningTimeout
     * @throws \Exception
     */
    public function generateHeadMetaTags_ValueComputedCorrectly_SessionWarningTimeout()
    {
        $sut = $this->setupSessionTimeoutWarning(true, $secondsBeforeExpiryWarning=60);
        $metaContent = $this->getMetaTagContent($sut->generateHeadMetaTags(), SessionTimeoutWarning::META_TAG_NAME_SESSION_WARNING_TIMEOUT);

        $this->assertIsNumeric($metaContent);
        $this->assertEquals(($this->sessionGcMaxLifeTime - $secondsBeforeExpiryWarning), $metaContent);
    }

    /**
     * @test
     * @depends generateHeadMetaTags_ConfigSetToEnabled_RendersMetaTags
     * @throws \Exception
     */
    public function generateHeadMetaTags_IsDefined_SessionRedirectTimeout()
    {
        $sut = $this->setupSessionTimeoutWarning(true, 60, $secondsAppendToSessionTimeout=10);
        $metaContent = $this->getMetaTagContent($sut->generateHeadMetaTags(), SessionTimeoutWarning::META_TAG_NAME_SESSION_REDIRECT_TIMEOUT);

        $this->assertNotEmpty($metaContent);
    }

    /**
     * @test
     * @depends generateHeadMetaTags_IsDefined_SessionRedirectTimeout
     * @throws \Exception
     */
    public function generateHeadMetaTags_ValueComputedCorrectly_SessionRedirectTimeout()
    {
        $sut = $this->setupSessionTimeoutWarning(true, 60);
        $metaContent = $this->getMetaTagContent($sut->generateHeadMetaTags(), SessionTimeoutWarning::META_TAG_NAME_SESSION_REDIRECT_TIMEOUT);

        $this->assertEquals(($this->sessionGcMaxLifeTime), $metaContent);
    }

    /**
     * @test
     * @depends generateHeadMetaTags_ConfigSetToEnabled_RendersMetaTags
     * @throws \Exception
     */
    public function generateHeadMetaTags_IsDefined_TimeoutRedirectUrl()
    {
        $sut = $this->setupSessionTimeoutWarning(true, 60, $timeoutRedirectUrl='/session-timeout');
        $metaContent = $this->getMetaTagContent($sut->generateHeadMetaTags(), SessionTimeoutWarning::META_TAG_NAME_TIMEOUT_REDIRECT_URL);

        $this->assertNotEmpty($metaContent);
    }

    /**
     * @test
     * @depends generateHeadMetaTags_IsDefined_TimeoutRedirectUrl
     * @throws \Exception
     */
    public function generateHeadMetaTags_ValueComputedCorrectly_TimeoutRedirectUrl()
    {
        $sut = $this->setupSessionTimeoutWarning(true, 60, $timeoutRedirectUrl='/session-timeout');
        $metaContent = $this->getMetaTagContent($sut->generateHeadMetaTags(), SessionTimeoutWarning::META_TAG_NAME_TIMEOUT_REDIRECT_URL);

        $this->assertEquals($timeoutRedirectUrl, $metaContent);
    }

    /**
     * Returns the content value of a meta tag given a string of HTML and a meta tag name.
     *
     * @param $html string The HTML string containing META tags.
     * @param $name string The meta tag name to obtain the value from.
     * @return string
     * @throws \Exception
     */
    private function getMetaTagContent(string $html, string $name): string
    {
        $document = new DOMDocument();
        $document->loadHTML($html);

        $metaTags = $document->getElementsByTagName('meta');
        foreach($metaTags as $metaTag) {
            assert($metaTag instanceof DOMNode);
            if ($metaTag->attributes->getNamedItem('name')->nodeValue === $name) {
                return $metaTag->attributes->getNamedItem('content')->nodeValue;
            }
        }

        throw new \Exception("No meta tag was found with the name " . $name);
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
        bool $enabled = false,
        int $secondsBeforeExpiryWarning = 60,
        string $timeoutRedirectUrl = 'some-url'
    ): SessionTimeoutWarning
    {
        $sut = new SessionTimeoutWarning(
            $enabled,
            $secondsBeforeExpiryWarning,
            $timeoutRedirectUrl
        );

        $mockedView = m::mock(RendererInterface::class)->asUndefined();
        assert($mockedView instanceof RendererInterface);
        $sut->setView($mockedView);

        return $sut;
    }
}
