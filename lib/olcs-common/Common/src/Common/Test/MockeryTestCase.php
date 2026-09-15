<?php

declare(strict_types=1);

namespace Common\Test;

use Mockery as m;

class MockeryTestCase extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * Configuration for mockery to allow/disallow mocks to mock methods that do not exist when mocking a class.
     *
     * This value will only be applied during the running of tests within this test suite; it does however change a
     * global configuration value while the tests in a test suite are being run.
     *
     * Enabling this could allow methods that do not exist to become callable, and therefore will fail when called in
     * production but in test would pass; for this reason i would only recommend enabling it if and when you have a need
     * for it.
     *
     * @see http://docs.mockery.io/en/latest/mockery/configuration.html
     * @var bool
     */
    protected $allowMockingNonExistentMethods = false;

    /**
     * @var bool|null
     */
    private $originalAllowMockingNonExistingMethods;

    /**
     * @return void
     */
    #[\Override]
    protected function mockeryTestSetUp()
    {
        $this->configureMockeryGlobalConfiguration();
        parent::mockeryTestSetUp();
    }

    /**
     * @return void
     */
    #[\Override]
    protected function mockeryTestTearDown()
    {
        $this->restoreMockeryGlobalConfiguration();
        parent::mockeryTestTearDown();
    }

    /**
     * Configures any custom global configuration that should be applied during the running of a test suite.
     *
     * Any configuration applied will be reset to the original value when tearing down a test case.
     */
    private function configureMockeryGlobalConfiguration(): void
    {
        $globalMockeryConfig = m::getConfiguration();

        // Configure the mocking of non-existing methods
        $this->originalAllowMockingNonExistingMethods = $globalMockeryConfig->mockingNonExistentMethodsAllowed();
        $globalMockeryConfig->allowMockingNonExistentMethods($this->allowMockingNonExistentMethods);
    }

    /**
     * Restores any global configuration back to the settings that were present before a test case run.
     */
    private function restoreMockeryGlobalConfiguration(): void
    {
        $globalMockeryConfig = m::getConfiguration();
        if (null !== $this->originalAllowMockingNonExistingMethods) {
            $globalMockeryConfig->allowMockingNonExistentMethods($this->originalAllowMockingNonExistingMethods);
        }
    }
}
