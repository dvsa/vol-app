<?php

namespace CommonTest\Preference;

use Common\Preference\LanguageListener;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Request;
use Laminas\Mvc\MvcEvent;

class LanguageListenerTest extends MockeryTestCase
{
    protected $languagePref;

    protected $flashMessenger;

    protected $translator;

    /**
     * @var LanguageListener
     */
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->languagePref = m::mock();
        $this->flashMessenger = m::mock();
        $this->translator = m::mock();

        $sm = new ServiceManager();

        $sm->setService('LanguagePreference', $this->languagePref);
        $sm->setService('Helper\FlashMessenger', $this->flashMessenger);
        $sm->setService('translator', $this->translator);

        $this->sut = new LanguageListener();
        $this->sut->__invoke($sm, LanguageListener::class);
    }

    public function testAttach(): void
    {
        $eventManager = m::mock(EventManagerInterface::class);
        $eventManager->shouldReceive('attach')
            ->once()
            ->with(MvcEvent::EVENT_ROUTE, [$this->sut, 'onRoute'], 1)
            ->andReturn('foo');

        $this->sut->attach($eventManager);
    }

    public function testOnRoute(): void
    {
        self::expectNotToPerformAssertions();

        $request = m::mock();

        $e = m::mock(MvcEvent::class);
        $e->shouldReceive('getRequest')
            ->andReturn($request);

        $this->sut->onRoute($e);
    }

    public function testOnRouteWithHttpRequest(): void
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('getQuery')
            ->with('lang')
            ->andReturn(null);

        $e = m::mock(MvcEvent::class);
        $e->shouldReceive('getRequest')
            ->andReturn($request);

        $this->languagePref->shouldReceive('getPreference')
            ->once()
            ->andReturn('en');

        $this->translator->shouldReceive('setLocale')
            ->once()
            ->with('en_GB');

        $this->sut->onRoute($e);
    }

    public function testOnRouteWithHttpRequestWithLang(): void
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('getQuery')
            ->with('lang')
            ->andReturn('cy');

        $e = m::mock(MvcEvent::class);
        $e->shouldReceive('getRequest')
            ->andReturn($request);

        $this->languagePref->shouldReceive('setPreference')
            ->once()
            ->andReturn('cy')
            ->shouldReceive('getPreference')
            ->once()
            ->andReturn('cy');

        $this->translator->shouldReceive('setLocale')
            ->once()
            ->with('cy_GB');

        $this->sut->onRoute($e);
    }

    public function testOnRouteWithHttpRequestWithLangWithException(): void
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('getQuery')
            ->with('lang')
            ->andReturn('cy');

        $e = m::mock(MvcEvent::class);
        $e->shouldReceive('getRequest')
            ->andReturn($request);

        $this->languagePref->shouldReceive('setPreference')
            ->once()
            ->andThrow('\Exception')
            ->shouldReceive('getPreference')
            ->once()
            ->andReturn('cy');

        $this->flashMessenger->shouldReceive('addCurrentErrorMessage')
            ->once()
            ->with('Only English and Welsh languages are supported');

        $this->translator->shouldReceive('setLocale')
            ->once()
            ->with('cy_GB');

        $this->sut->onRoute($e);
    }
}
