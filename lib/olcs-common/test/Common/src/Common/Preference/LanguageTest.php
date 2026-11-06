<?php

declare(strict_types=1);

namespace CommonTest\Preference;

use Common\Preference\Language;
use Laminas\Http\Headers;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Http\Header\Cookie;
use Laminas\Http\Header\SetCookie;
use Laminas\Http\Request;
use Laminas\Http\Response;

final class LanguageTest extends MockeryTestCase
{
    /**
     * @var Language
     */
    protected $sut;

    protected $sm;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var SetCookie
     */
    protected $setCookie;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new Language();

        $this->sm = new ServiceManager();

        $this->request = m::mock(Request::class);
        $this->response = m::mock(Response::class);

        $this->sm->setService('Request', $this->request);
        $this->sm->setService('Response', $this->response);
    }

    private function expectCookieHeader(): void
    {
        $headers = m::mock(Headers::class);
        $headers->expects('addHeader')
            ->with(m::type(SetCookie::class))
            ->andReturnUsing(
                function ($cookie) use ($headers) {
                    $this->setCookie = $cookie;
                    return $headers;
                }
            );

        $this->response->expects('getHeaders')->withNoArgs()->andReturn($headers);
    }

    private function setSettingsConsent(mixed $cookie, bool $isActive): void
    {
        $cookieState = m::mock();
        $cookieState->expects('isActive')->with('settings')->andReturn($isActive);

        $cookieReader = m::mock();
        $cookieReader->expects('getState')->with($cookie)->andReturn($cookieState);

        $this->sm->setService('CookieCookieReader', $cookieReader);
    }

    private function cookieWithLanguage(string $language): Cookie
    {
        $cookie = m::mock(Cookie::class)->makePartial();
        $cookie['langPref'] = $language;

        return $cookie;
    }

    public function testInvoke(): void
    {
        $this->expectCookieHeader();

        $cookie = m::mock();

        $this->request->expects('getCookie')->withNoArgs()->andReturn($cookie);

        $this->sut->__invoke($this->sm, SetCookie::class);

        $this->assertInstanceOf(SetCookie::class, $this->setCookie);

        $this->assertEquals('en', $this->setCookie->getValue());
    }

    public function testInvokeWithCookie(): void
    {
        $this->expectCookieHeader();

        $this->request->expects('getCookie')->withNoArgs()->andReturn($this->cookieWithLanguage('cy'));

        $this->sut->__invoke($this->sm, SetCookie::class);

        $this->assertInstanceOf(SetCookie::class, $this->setCookie);

        $this->assertEquals('cy', $this->setCookie->getValue());
        $this->assertEquals('Strict', $this->setCookie->getSameSite());
    }

    public function testInvokeIgnoresUnknownLanguageInCookie(): void
    {
        $this->expectCookieHeader();

        $this->request->expects('getCookie')->withNoArgs()->andReturn($this->cookieWithLanguage('XX'));

        $this->sut->__invoke($this->sm, SetCookie::class);

        $this->assertEquals('en', $this->setCookie->getValue());
        $this->assertEquals('en', $this->sut->getPreference());
    }

    public function testInvokeWithSettingsConsentKeepsLanguageForNextVisit(): void
    {
        $this->expectCookieHeader();

        $cookie = $this->cookieWithLanguage('cy');
        $this->request->expects('getCookie')->withNoArgs()->andReturn($cookie);
        $this->setSettingsConsent($cookie, true);

        $this->sut->__invoke($this->sm, SetCookie::class);

        $this->assertEquals('cy', $this->setCookie->getValue());
        $this->assertEquals('cy', $this->sut->getPreference());
        $this->assertGreaterThan(strtotime('+9 years'), $this->setCookie->getExpires(true));
    }

    public function testInvokeWithoutSettingsConsentKeepsChosenLanguageUntilBrowserCloses(): void
    {
        $this->expectCookieHeader();

        $cookie = $this->cookieWithLanguage('cy');
        $this->request->expects('getCookie')->withNoArgs()->andReturn($cookie);
        $this->setSettingsConsent($cookie, false);

        $this->sut->__invoke($this->sm, SetCookie::class);

        $this->assertEquals('cy', $this->sut->getPreference());
        $this->assertEquals('cy', $this->setCookie->getValue());
        $this->assertNull($this->setCookie->getExpires());
        $this->assertEquals('Strict', $this->setCookie->getSameSite());
    }

    public function testInvokeWithoutSettingsConsentOrChoiceSetsNoCookie(): void
    {
        $this->response->shouldNotReceive('getHeaders');

        $cookie = m::mock();
        $this->request->expects('getCookie')->withNoArgs()->andReturn($cookie);
        $this->setSettingsConsent($cookie, false);

        $this->sut->__invoke($this->sm, SetCookie::class);

        $this->assertEquals('en', $this->sut->getPreference());
    }

    public function testSetPreferenceWithoutSettingsConsentSetsSessionCookie(): void
    {
        $this->expectCookieHeader();

        $cookie = m::mock();
        $this->request->expects('getCookie')->withNoArgs()->andReturn($cookie);
        $this->setSettingsConsent($cookie, false);

        $this->sut->__invoke($this->sm, SetCookie::class);
        $this->sut->setPreference('cy');

        $this->assertEquals('cy', $this->sut->getPreference());
        $this->assertEquals('cy', $this->setCookie->getValue());
        $this->assertNull($this->setCookie->getExpires());
    }

    public function testSetPreferenceException(): void
    {
        $this->expectCookieHeader();

        $this->request->expects('getCookie')->withNoArgs()->andReturn($this->cookieWithLanguage('cy'));

        $this->sut->__invoke($this->sm, SetCookie::class);

        $this->expectException('\Exception');

        $this->sut->setPreference('XX');
    }

    public function testSetPreference(): void
    {
        $this->expectCookieHeader();

        $this->request->expects('getCookie')->withNoArgs()->andReturn($this->cookieWithLanguage('cy'));

        $this->sut->__invoke($this->sm, SetCookie::class);

        $this->sut->setPreference('en');

        $this->assertEquals('en', $this->setCookie->getValue());
        $this->assertEquals('en', $this->sut->getPreference());
    }
}
