<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterType;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get list of LetterTypes
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'LetterType';
}