<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Base for tests that load or boot the real api (see ApiApplication).
 *
 * Captures the process-wide state before each test and restores it afterwards, so booting the
 * application in the unit suite leaves nothing behind for the tests that run after it.
 */
abstract class FrameworkTestCase extends MockeryTestCase
{
    private ?GlobalState $globalState = null;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->globalState = GlobalState::capture();
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->globalState?->restore();
        $this->globalState = null;

        parent::tearDown();
    }
}
