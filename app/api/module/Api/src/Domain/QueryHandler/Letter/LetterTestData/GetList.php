<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterTestData;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Get list of LetterTestDatas
 */
class GetList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'LetterTestData';
}
