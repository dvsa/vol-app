<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpCandidatePermit;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpCandidatePermit\ById as IrhpCandidatePermitByIdHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepo;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\ById as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractQueryByIdHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit as IrhpCandidatePermitEntity;

/**
 * ById Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class ByIdTest extends AbstractQueryByIdHandlerTestCase
{
    protected $sutClass = IrhpCandidatePermitByIdHandler::class;
    protected $sutRepo = 'IrhpCandidatePermit';
    protected $bundle = ['irhpPermitRange' => ['countrys'], 'irhpPermitApplication'];
    protected $qryClass = QryClass::class;
    protected $repoClass = IrhpCandidatePermitRepo::class;
    protected $entityClass = IrhpCandidatePermitEntity::class;
}
