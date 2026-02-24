<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cookie\CookieReader;
use Olcs\Service\Cookie\CookieState;
use Olcs\Service\Cookie\CookieStateFactory;
use Olcs\Service\Cookie\Preferences;
use Olcs\Service\Cookie\PreferencesFactory;
use RuntimeException;
use Laminas\Http\Header\Cookie;

class CookieReaderTest extends MockeryTestCase
{
    private $cookieState;

    private $cookieStateFactory;

    private $preferencesFactory;

    private $sut;

    public function setUp(): void
    {
        $this->cookieState = m::mock(CookieState::class);

        $this->cookieStateFactory = m::mock(CookieStateFactory::class);

        $this->preferencesFactory = m::mock(PreferencesFactory::class);

        $this->sut = new CookieReader($this->cookieStateFactory, $this->preferencesFactory);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpFalseCookieStateWhenNotCookieObject')]
    public function testFalseCookieStateWhenNotCookieObject(mixed $cookie): void
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

    /**
     * @return (\stdClass|null|string)[][]
     *
     * @psalm-return list{list{null}, list{\stdClass}, list{'cookie'}}
     */
    public static function dpFalseCookieStateWhenNotCookieObject(): array
    {
        return [
            [null],
            [new \stdClass()],
            ['cookie'],
        ];
    }

    public function testFalseCookieStateWhenInvalidJson(): void
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

    public function testFalseCookieStateWhenInvalidDecodedJsonContent(): void
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

    public function testTrueCookieState(): void
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
