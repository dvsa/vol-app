<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpPermitRange;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitRange\ById as RangeByIdHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as PermitRangeRepo;
use Dvsa\Olcs\Transfer\Query\IrhpPermitRange\ById as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractQueryByIdHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange as PermitRangeEntity;

/**
 * ById Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class ByIdTest extends AbstractQueryByIdHandlerTestCase
{
    protected $sutClass = RangeByIdHandler::class;
    protected $sutRepo = 'IrhpPermitRange';
    protected $bundle = ['countrys', 'irhpPermitStock', 'emissionsCategory', 'journey'];
    protected $qryClass = QryClass::class;
    protected $repoClass = PermitRangeRepo::class;
    protected $entityClass = PermitRangeEntity::class;
}
