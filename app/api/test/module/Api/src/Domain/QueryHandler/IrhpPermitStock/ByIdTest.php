<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpPermitStock;

use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitStock\ById as StockByIdHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as PermitStockRepo;
use Dvsa\Olcs\Transfer\Query\IrhpPermitStock\ById as QryClass;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\AbstractQueryByIdHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as PermitStockEntity;

/**
 * ById Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class ByIdTest extends AbstractQueryByIdHandlerTestCase
{
    protected $sutClass = StockByIdHandler::class;
    protected $sutRepo = 'IrhpPermitStock';
    protected $bundle = [
        'irhpPermitType' => ['name'],
        'country',
        'applicationPathGroup',
        'irhpPermitRanges' => ['emissionsCategory']
    ];
    protected $qryClass = QryClass::class;
    protected $repoClass = PermitStockRepo::class;
    protected $entityClass = PermitStockEntity::class;
}
