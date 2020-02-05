<?php

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\CookieReader;
use Olcs\Service\Cookie\CookieState;
use Olcs\Service\Cookie\CookieStateFactory;
use Olcs\Service\Cookie\Preferences;
use Olcs\Service\Cookie\PreferencesFactory;
use RuntimeException;
use Zend\Http\Header\Cookie;

class CookieReaderTest extends MockeryTestCase
{
    private $cookieState;

    private $cookieStateFactory;

    private $preferencesFactory;

    private $sut;

    public function setUp()
    {
        $this->cookieState = m::mock(CookieState::class);

        $this->cookieStateFactory = m::mock(CookieStateFactory::class);

        $this->preferencesFactory = m::mock(PreferencesFactory::class);

        $this->sut = new CookieReader($this->cookieStateFactory, $this->preferencesFactory);
    }

    /**
     * @dataProvider dpFalseCookieStateWhenNotCookieObject
     */
    public function testFalseCookieStateWhenNotCookieObject($cookie)
    {
        $this->cookieStateFactory->shouldReceive('create')
            ->with(false)
            ->once()
            ->andReturn($this->cookieState);

        $this->assertSame(
            $this->cookieState,
            $this->sut->getState($cookie)
        );
    }

    public function dpFalseCookieStateWhenNotCookieObject()
    {
        return [
            [null],
            [new \stdClass()],
            ['cookie'],
        ];
    }

    public function testFalseCookieStateWhenInvalidJson()
    {
        $json = '{"field1"=>"value1", "field2&:"value2"}';
        $cookie = new Cookie([Preferences::COOKIE_NAME => $json]);

        $this->cookieStateFactory->shouldReceive('create')
            ->with(false)
            ->once()
            ->andReturn($this->cookieState);

        $this->assertSame(
            $this->cookieState,
            $this->sut->getState($cookie)
        );
    }

    public function testFalseCookieStateWhenInvalidDecodedJsonContent()
    {
        $json = '{"field1":"value1","field2":"value2"}';
        $decodedJson = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $cookie = new Cookie([Preferences::COOKIE_NAME => $json]);

        $this->cookieStateFactory->shouldReceive('create')
            ->with(false)
            ->once()
            ->andReturn($this->cookieState);

        $this->preferencesFactory->shouldReceive('create')
            ->with($decodedJson)
            ->once()
            ->andThrow(new RuntimeException());

        $this->assertSame(
            $this->cookieState,
            $this->sut->getState($cookie)
        );
    }

    public function testTrueCookieState()
    {
        $json = '{"settings":true,"analytics":false}';
        $decodedJson = [
            'settings' => true,
            'analytics' => false,
        ];

        $cookie = new Cookie([Preferences::COOKIE_NAME => $json]);

        $preferences = m::mock(Preferences::class);

        $this->preferencesFactory->shouldReceive('create')
            ->with($decodedJson)
            ->once()
            ->andReturn($preferences);

        $this->cookieStateFactory->shouldReceive('create')
            ->with(true, $preferences)
            ->once()
            ->andReturn($this->cookieState);

        $this->assertSame(
            $this->cookieState,
            $this->sut->getState($cookie)
        );
    }
}
