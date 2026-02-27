<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Cases;

use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Cases\Statement as Entity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Mockery as m;

/**
 * Statement Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class StatementEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetRelatedOrganisation(): void
    {
        $mockCase = m::mock(CasesEntity::class);
        $mockStatementType = m::mock(RefDataEntity::class);

        $mockOrganisation = m::mock(OrganisationEntity::class);

        $mockCase->shouldReceive('getRelatedOrganisation')->andReturn($mockOrganisation);

        $sut = new Entity($mockCase, $mockStatementType);

        $sut->setCase($mockCase);

        $this->assertEquals($mockOrganisation, $sut->getRelatedOrganisation());
    }
}
