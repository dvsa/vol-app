<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\PresidingTc;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\PresidingTc\ById as PresidingTcByIdHandler;
use Dvsa\Olcs\Api\Domain\Repository\PresidingTc as PresidingTcRepo;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as PresidingTcEntity;
use Dvsa\Olcs\Transfer\Query\Cases\PresidingTc\ById as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractQueryByIdHandlerTestCase;

/**
 * ById Test
 *
 */
class ByIdTest extends AbstractQueryByIdHandlerTestCase
{
    protected $sutClass = PresidingTcByIdHandler::class;
    protected $sutRepo = 'PresidingTc';
    protected $bundle = ['user'];
    protected $qryClass = QryClass::class;
    protected $repoClass = PresidingTcRepo::class;
    protected $entityClass = PresidingTcEntity::class;
}
