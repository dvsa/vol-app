<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\FeatureToggle;

use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\FeatureToggle\IsEnabled as IsEnabledHandler;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled as IsEnabledQry;
use Mockery as m;

/**
 * Tests the feature toggle (is enabled) query handler
 */
class IsEnabledTest extends QueryHandlerTestCase
{
    protected $qry;

    public function setUp(): void
    {
        $this->sut = new IsEnabledHandler();
        $this->qry = IsEnabledQry::create(['ids' => ['toggle1', 'toggle2']]);

        $this->mockedSmServices = [
            ToggleService::class => m::mock(ToggleService::class)
        ];

        parent::setUp();
    }

    public function testHandleCommandWithAllEnabled(): void
    {
        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')
            ->with('toggle1')
            ->once()
            ->andReturn(true);

        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')
            ->with('toggle2')
            ->once()
            ->andReturn(true);

        $this->assertEquals(true, $this->sut->handleQuery($this->qry)['isEnabled']);
    }

    public function testHandleCommandWithLastOneDisabled(): void
    {
        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')
            ->with('toggle1')
            ->once()
            ->andReturn(true);

        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')
            ->with('toggle2')
            ->once()
            ->andReturn(false);

        $this->assertEquals(false, $this->sut->handleQuery($this->qry)['isEnabled']);
    }

    public function testHandleCommandWithFirstDisabled(): void
    {
        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')
            ->with('toggle1')
            ->once()
            ->andReturn(false);

        $this->mockedSmServices[ToggleService::class]
            ->shouldReceive('isEnabled')
            ->with('toggle2')
            ->never();

        $this->assertEquals(false, $this->sut->handleQuery($this->qry)['isEnabled']);
    }
}
