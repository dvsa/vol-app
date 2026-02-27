<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain;

use Dvsa\Olcs\Api\Domain\Exception\DisabledHandlerException;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\HandlerEnabledTestStub;
use Mockery as m;

/**
 * test the handler enabled trait
 */
class HandlerEnabledTraitTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var HandlerEnabledTraitStub
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new HandlerEnabledTestStub();

        $this->mockedSmServices = [
            ToggleService::class => m::mock(ToggleService::class)
        ];

        parent::setUp();
    }

    public function testEnabledWithHandlerFqdn(): void
    {
        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')
            ->once()
            ->with('CommandHandler\HandlerEnabledTestStub')
            ->andReturn(true);

        $this->assertEquals(true, $this->sut->checkEnabled());
    }

    public function testEnabledWithHandlerFqdnException(): void
    {
        $this->expectException(DisabledHandlerException::class);

        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')
            ->once()
            ->with('CommandHandler\HandlerEnabledTestStub')
            ->andReturn(false);

        $this->sut->checkEnabled();
    }

    public function testEnabledWithToggleConfig(): void
    {
        $toggles = ['toggle1', 'toggle2'];
        $this->sut->setToggleConfig($toggles);

        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')
            ->once()
            ->with('toggle1')
            ->andReturn(true);

        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')
            ->once()
            ->with('toggle2')
            ->andReturn(true);

        $this->assertEquals(true, $this->sut->checkEnabled());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('toggleConfigProvider')]
    public function testEnabledWithToggleConfigException(mixed $toggleConfig1, mixed $toggleConfig2, mixed $checkSecondToggle): void
    {
        $this->expectException(DisabledHandlerException::class);

        $toggles = ['toggle1', 'toggle2'];
        $this->sut->setToggleConfig($toggles);

        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')
            ->once()
            ->with('toggle1')
            ->andReturn($toggleConfig1);

        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')
            ->times($checkSecondToggle)
            ->with('toggle2')
            ->andReturn($toggleConfig2);

        $this->assertEquals(true, $this->sut->checkEnabled());
    }

    public static function toggleConfigProvider(): array
    {
        return [
            [true, false, 1],
            [false, true, 0]
        ];
    }
}
