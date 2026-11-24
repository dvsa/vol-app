<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

use Laminas\ServiceManager\ServiceManager;
use Psr\Http\Message\ServerRequestInterface;

/**
 * The api's HTTP front door, as a test sees it: a PSR-7 request in, the PSR-7 response the client would
 * receive out.
 *
 * HttpContractTest is written against this interface, not against laminas-mvc, so the same cases can run
 * against whatever replaces it. MvcHttpKernel is today's implementation; a PSR-15 one for Mezzio is the
 * next, and while both stacks exist the contract runs against each.
 */
interface HttpKernel
{
    /**
     * @param (\Closure(ServiceManager): void)|null $configure replaces services for this request, as
     *                                                        ApiApplication::bootstrap() describes
     */
    public function handle(ServerRequestInterface $request, ?\Closure $configure = null): HttpExchange;
}
