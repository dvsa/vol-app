<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

/**
 * The two ways the api is started: over HTTP (config/container.php, public/index.php) and from the
 * command line (config/container-cli.php — laminas-cli, the queue processors and every AWS Batch job).
 */
enum Entrypoint: string
{
    case Web = 'web';
    case Cli = 'cli';
}
