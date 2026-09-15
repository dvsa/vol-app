<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\LongText;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

final class ById extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'LongText';
}
