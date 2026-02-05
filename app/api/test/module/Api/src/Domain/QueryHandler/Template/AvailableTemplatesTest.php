<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\Template\AvailableTemplates as Handler;
use Dvsa\Olcs\Api\Domain\Repository\Template as Repo;
use Dvsa\Olcs\Transfer\Query\Template\AvailableTemplates as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractListQueryHandlerTestCase;

/**
 * AvailableTemplates Test
 */
class AvailableTemplatesTest extends AbstractListQueryHandlerTestCase
{
    protected $sutClass = Handler::class;
    protected $sutRepo = 'Template';
    protected $qryClass = Query::class;
    protected $repoClass = Repo::class;
}
