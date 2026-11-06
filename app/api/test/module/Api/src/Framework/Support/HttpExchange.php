<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

use Monolog\LogRecord;
use Psr\Http\Message\ResponseInterface;

/**
 * What one request through an HttpKernel produced: the response, and what was logged while handling it.
 *
 * If the request never produced a response — the exception escaped the application, which today happens
 * when bootstrap itself throws — $uncaught holds it instead, and the process-level handlers decide what
 * the client gets. Reading the code rather than observing it: Monolog's uncaught-exception handler (installed
 * by the logging bootstrap) logs it, sets 500 when display_errors is off, and exits, so the client gets an
 * empty 500; public/index.php's JSON fatal-error body is not reached, because no PHP error is recorded.
 */
final class HttpExchange
{
    /**
     * @param list<LogRecord> $logs
     */
    public function __construct(
        public readonly ?ResponseInterface $response,
        public readonly array $logs,
        public readonly ?\Throwable $uncaught = null,
    ) {
    }
}
