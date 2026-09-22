<?php

declare(strict_types=1);

namespace CommonTest\Controller\Lva;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Controller\Lva\AbstractController;
use Common\Controller\Plugin\FeaturesEnabled;
use Common\Service\Cqrs\Query\QuerySender;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\Plugin\CreateHttpNotFoundModel;
use Laminas\Mvc\Exception\DomainException;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AbstractControllerTest extends MockeryTestCase
{
    #[DataProvider('dispatchProvider')]
    public function testToggleGateIsOptInAndCannotBeSkipped(
        bool $toggleAware,
        bool $enabled,
        bool $skipPreDispatch
    ): void {
        $sut = $this->createController($toggleAware);
        $event = $this->createEvent($sut, $skipPreDispatch);

        if ($toggleAware) {
            $sut->shouldReceive('featuresEnabled')->with([], $event)->once()->andReturn($enabled);
        } else {
            $sut->shouldNotReceive('featuresEnabled');
        }

        $allowed = !$toggleAware || $enabled;
        if ($allowed) {
            $view = new ViewModel();
            $sut->shouldReceive('maybeTranslateForNi')->once();
            $sut->shouldReceive('indexAction')->once()->andReturn($view);
            $sut->shouldNotReceive('notFoundAction');
            if (!$skipPreDispatch) {
                $sut->shouldReceive('preDispatch')->once()->andReturnNull();
            } else {
                $sut->shouldNotReceive('preDispatch');
            }
        } else {
            $sut->shouldNotReceive('maybeTranslateForNi');
            $sut->shouldNotReceive('preDispatch');
            $sut->shouldNotReceive('indexAction');
            $this->expectNotFoundPlugin($sut);
        }

        $result = $sut->onDispatch($event);

        self::assertSame($result, $event->getResult());
        self::assertSame($allowed ? 200 : 404, $event->getResponse()->getStatusCode());
        if ($allowed) {
            self::assertSame($view, $result);
        }
    }

    public function testToggleAwareControllerWithoutConfigurationIsDisabled(): void
    {
        $sut = $this->createController(true);
        $event = $this->createEvent($sut, false);
        $querySender = m::mock(QuerySender::class);
        $querySender->shouldNotReceive('featuresEnabled');
        // Exercise the real plugin: an unconfigured opted-in controller is denied by default.
        $sut->shouldReceive('plugin')->with('featuresEnabled')->once()->andReturn(new FeaturesEnabled($querySender));
        $sut->shouldNotReceive('maybeTranslateForNi');
        $sut->shouldNotReceive('preDispatch');
        $sut->shouldNotReceive('indexAction');
        $this->expectNotFoundPlugin($sut);

        $result = $sut->onDispatch($event);

        self::assertSame(404, $event->getResponse()->getStatusCode());
        self::assertSame($result, $event->getResult());
    }

    public function testEnabledControllerPreservesPreDispatchResponse(): void
    {
        $sut = $this->createController(true);
        $event = $this->createEvent($sut, false);
        $redirect = new Response();
        $redirect->setStatusCode(302);
        $sut->shouldReceive('featuresEnabled')->with([], $event)->once()->andReturnTrue();
        $sut->shouldReceive('maybeTranslateForNi')->once();
        $sut->shouldReceive('preDispatch')->once()->andReturn($redirect);
        $sut->shouldNotReceive('indexAction');

        self::assertSame($redirect, $sut->onDispatch($event));
        self::assertSame($redirect, $event->getResult());
    }

    public function testMissingRouteMatchStillThrows(): void
    {
        $sut = $this->createController(true);
        $sut->shouldNotReceive('featuresEnabled');

        $this->expectException(DomainException::class);
        $sut->onDispatch(new MvcEvent());
    }

    public static function dispatchProvider(): array
    {
        return [
            'enabled' => [true, true, false],
            'enabled with pre-dispatch skipped' => [true, true, true],
            'disabled' => [true, false, false],
            'disabled with pre-dispatch skipped' => [true, false, true],
            'not toggle-aware' => [false, false, false],
            'not toggle-aware with pre-dispatch skipped' => [false, false, true],
        ];
    }

    private function createController(bool $toggleAware): AbstractController
    {
        $class = AbstractController::class;
        if ($toggleAware) {
            $class .= ', ' . ToggleAwareInterface::class;
        }

        return m::mock($class)->makePartial()->shouldAllowMockingProtectedMethods();
    }

    private function createEvent(AbstractController $sut, bool $skipPreDispatch): MvcEvent
    {
        $event = new MvcEvent();
        $event->setRouteMatch(new RouteMatch(['action' => 'index', 'skipPreDispatch' => $skipPreDispatch]));
        $event->setResponse(new Response());
        $sut->setEvent($event);

        return $event;
    }

    private function expectNotFoundPlugin(AbstractController $sut): void
    {
        $sut->shouldReceive('plugin')
            ->with('createHttpNotFoundModel')
            ->once()
            ->andReturn(new CreateHttpNotFoundModel());
    }
}
