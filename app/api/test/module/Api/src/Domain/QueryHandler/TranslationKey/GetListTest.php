<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\QueryHandler\TranslationKey\GetList as Handler;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKey as Repo;
use Dvsa\Olcs\Transfer\Query\TranslationKey\GetList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractListQueryHandlerTest;

/**
 * GetList Test
 */
class GetListTest extends AbstractListQueryHandlerTest
{
    protected $sutClass = Handler::class;
    protected $sutRepo = 'TranslationKey';
    protected $qryClass = Query::class;
    protected $repoClass = Repo::class;
}
