<?php

declare(strict_types=1);

namespace OlcsTest\Traits;

use Mockery;

/**
 * mixin for test cases that need Mockery but extend a different abstract
 */
trait MockeryTestCaseTrait
{
    protected function assertPostConditions(): void
    {
        $this->addMockeryExpectationsToAssertionCount();
        $this->closeMockery();

        parent::assertPostConditions();
    }

    protected function addMockeryExpectationsToAssertionCount(): void
    {
        $container = Mockery::getContainer();
        if ($container != null) {
            $count = $container->mockery_getExpectationCount();
            $this->addToAssertionCount($count);
        }
    }

    protected function closeMockery(): void
    {
        Mockery::close();
    }
}
