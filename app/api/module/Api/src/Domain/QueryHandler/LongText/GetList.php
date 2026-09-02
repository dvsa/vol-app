<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\QueryHandler\LongText;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

final class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'LongText';
}
