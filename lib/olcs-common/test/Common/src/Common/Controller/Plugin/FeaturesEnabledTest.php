<?php

declare(strict_types=1);

namespace CommonTest\Controller\Plugin;

use Common\Controller\Plugin\FeaturesEnabled;
use Common\Service\Cqrs\Query\QuerySender;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Mvc\MvcEvent;

/**
 * FeaturesEnabled Test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
final class FeaturesEnabledTest extends MockeryTestCase
{
    protected $action = 'action';

    protected $mvcEvent;

    protected $querySender;

    #[\Override]
    protected function setUp(): void
    {
        $this->mvcEvent = m::mock(MvcEvent::class);
        $this->mvcEvent->shouldReceive('getRouteMatch->getParam')->with('action')->andReturn($this->action);
        $this->querySender = m::mock(QuerySender::class);
    }

    public function testInvokeWithEmptyConfig(): void
    {
        $this->querySender->shouldNotReceive('featuresEnabled');
        $sut = new FeaturesEnabled($this->querySender);
        $this->assertEquals(false, $sut->__invoke([], $this->mvcEvent));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestInvoke')]
    public function testInvoke($config, $checkedToggles, $expectedResult, $numChecks): void
    {
        $this->querySender->shouldReceive('featuresEnabled')->times($numChecks)->with($checkedToggles)->andReturn($expectedResult);
        $sut = new FeaturesEnabled($this->querySender);
        $this->assertEquals($expectedResult, $sut->__invoke($config, $this->mvcEvent));
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<string> | string)> | bool | int)>>
     *
     * @psalm-return list{list{array{default: list{'default toggle 1', 'default toggle 2'},...}, list{'action toggle 1', 'action toggle 2'}, true, 1}, list{array{default: list{'default toggle 1', 'default toggle 2'}}, list{'default toggle 1', 'default toggle 2'}, false, 1}, list{array<list{'action toggle 1', 'action toggle 2'}>, list{'action toggle 1', 'action toggle 2'}, true, 1}, list{array<array<never, never>>, array<never, never>, true, 0}, list{array{default: array<never, never>}, array<never, never>, true, 0}}
     */
    public static function dpTestInvoke(): \Iterator
    {
        $defaultConfig = ['default toggle 1', 'default toggle 2'];
        $actionConfig = ['action toggle 1', 'action toggle 2'];

        $bothConfigs = [
            'default' => $defaultConfig,
            'action' => $actionConfig
        ];

        $defaultConfigOnly = [
            'default' => $defaultConfig
        ];

        //action config only
        $actionConfigOnly = [
            'action' => $actionConfig
        ];

        //empty action config
        $emptyActionConfig = [
            'action' => []
        ];

        //action config only
        $emptyDefaultConfig = [
            'default' => []
        ];
        yield [$bothConfigs, $actionConfig, true, 1];
        yield [$defaultConfigOnly, $defaultConfig, false, 1];
        yield [$actionConfigOnly, $actionConfig, true, 1];
        yield [$emptyActionConfig, [], true, 0];
        yield [$emptyDefaultConfig, [], true, 0];
    }
}
