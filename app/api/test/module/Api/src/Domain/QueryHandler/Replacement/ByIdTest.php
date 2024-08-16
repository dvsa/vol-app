<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Replacement;

use Dvsa\Olcs\Api\Domain\QueryHandler\Replacement\ById as ReplacementByIdHandler;
use Dvsa\Olcs\Api\Domain\Repository\Replacement as ReplacementRepo;
use Dvsa\Olcs\Transfer\Query\Replacement\ById as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractQueryByIdHandlerTest;
use Dvsa\Olcs\Api\Entity\System\Replacement as ReplacementEntity;

/**
 * ById Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class ByIdTest extends AbstractQueryByIdHandlerTest
{
    protected $sutClass = ReplacementByIdHandler::class;
    protected $sutRepo = 'Replacement';
    protected $qryClass = QryClass::class;
    protected $repoClass = ReplacementRepo::class;
    protected $entityClass = ReplacementEntity::class;
}
