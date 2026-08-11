<?php

declare(strict_types=1);

namespace OlcsTest\Controller;

use Olcs\Controller\RetrieveController;
use PHPUnit\Framework\TestCase;

/**
 * The presigned S3 fetch on the retrieve journey must be routed through the shared egress proxy in
 * deployed environments (the frontends have no direct outbound egress), yet fall back to a direct
 * connection locally where the proxy token is unresolved. These cases pin that resolution.
 *
 * @covers \Olcs\Controller\RetrieveController::presignedFetchProxyOptions
 */
final class RetrieveControllerTest extends TestCase
{
    public function testProxyOptionsUsesConfiguredProxy(): void
    {
        $this->assertSame(
            ['proxy' => 'http://proxy.app.olcs.dvsacloud.uk:3128'],
            RetrieveController::presignedFetchProxyOptions('http://proxy.app.olcs.dvsacloud.uk:3128'),
        );
    }

    public function testProxyOptionsIgnoresUnresolvedPlaceholder(): void
    {
        // Local / pre-resolution: the %shd_proxy% token is still literal, so connect directly.
        $this->assertSame([], RetrieveController::presignedFetchProxyOptions('http://%shd_proxy%'));
    }

    public function testProxyOptionsIgnoresEmptyString(): void
    {
        $this->assertSame([], RetrieveController::presignedFetchProxyOptions(''));
    }

    public function testProxyOptionsIgnoresNonString(): void
    {
        $this->assertSame([], RetrieveController::presignedFetchProxyOptions(null));
    }
}
