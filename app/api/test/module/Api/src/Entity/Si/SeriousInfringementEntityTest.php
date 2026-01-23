<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Si;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategory as SiCategoryEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategoryType as SiCategoryTypeEntity;
use Dvsa\Olcs\Api\Entity\Si\SiPenaltyErruRequested;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as Entity;
use Mockery as m;

/**
 * SeriousInfringement Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class SeriousInfringementEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Tests creation of a serious infringement
     */
    public function testCreate(): void
    {
        $case = m::mock(CaseEntity::class);
        $checkDate = new \DateTime('2015-12-25');
        $infringementDate = new \DateTime('2015-12-26');
        $siCategory = m::mock(SiCategoryEntity::class);
        $siCategoryType = m::mock(SiCategoryTypeEntity::class);

        $entity = new Entity(
            $case,
            $checkDate,
            $infringementDate,
            $siCategory,
            $siCategoryType
        );

        $this->assertEquals($case, $entity->getCase());
        $this->assertEquals($checkDate, $entity->getCheckDate());
        $this->assertEquals($infringementDate, $entity->getInfringementDate());
        $this->assertEquals($siCategory, $entity->getSiCategory());
        $this->assertEquals($siCategoryType, $entity->getSiCategoryType());
    }

    public function testResponseSetTrue(): void
    {
        $requestedErru1 = m::mock(SiPenaltyErruRequested::class);
        $requestedErru1->expects('hasAppliedPenalty')->withNoArgs()->andReturnTrue();

        $requestedErru2 = m::mock(SiPenaltyErruRequested::class);
        $requestedErru2->expects('hasAppliedPenalty')->withNoArgs()->andReturnTrue();

        $requestedErruCollection = new ArrayCollection([$requestedErru1, $requestedErru2]);

        /** @var Entity $entity */
        $entity = $this->instantiate(Entity::class);
        $entity->setRequestedErrus($requestedErruCollection);
        $this->assertTrue($entity->responseSet());
    }

    public function testResponseSetFalse(): void
    {
        //has applied penalty so the next one needs to be checked
        $requestedErru1 = m::mock(SiPenaltyErruRequested::class);
        $requestedErru1->expects('hasAppliedPenalty')->withNoArgs()->andReturnTrue();

        //no applied penalty so we know not all responses set
        $requestedErru2 = m::mock(SiPenaltyErruRequested::class);
        $requestedErru2->expects('hasAppliedPenalty')->withNoArgs()->andReturnFalse();

        //doesn't need to be checked
        $requestedErru3 = m::mock(SiPenaltyErruRequested::class);
        $requestedErru3->expects('hasAppliedPenalty')->never();

        $requestedErruCollection = new ArrayCollection([$requestedErru1, $requestedErru2, $requestedErru3]);

        /** @var Entity $entity */
        $entity = $this->instantiate(Entity::class);
        $entity->setRequestedErrus($requestedErruCollection);
        $this->assertFalse($entity->responseSet());
    }

    public function testResponseSetNoRequestedPenalties(): void
    {
        /** @var Entity $entity */
        $entity = $this->instantiate(Entity::class);
        $entity->setRequestedErrus(new ArrayCollection());
        $this->assertTrue($entity->responseSet());
    }

    /**
     * @dataProvider dpHasRequestedPenalties
     */
    public function testHasRequestedPenalties(ArrayCollection $requestedErrus, bool $expectedResult): void
    {
        /** @var Entity $entity */
        $entity = $this->instantiate(Entity::class);
        $entity->setRequestedErrus($requestedErrus);
        $this->assertEquals($expectedResult, $entity->hasRequestedPenalties());
    }

    public function dpHasRequestedPenalties(): array
    {
        return [
            [new ArrayCollection(), false],
            [new ArrayCollection([m::mock(SiPenaltyErruRequested::class)]), true]
        ];
    }

    public function testGetCalculatedBundleValues(): void
    {
        /** @var Entity $entity */
        $entity = $this->instantiate(Entity::class);
        $entity->setAppliedPenalties(new ArrayCollection());
        $entity->setRequestedErrus(new ArrayCollection());
        $this->assertEquals(
            [
                'responseSet' => true,
                'hasRequestedPenalties' => false,
            ],
            $entity->getCalculatedBundleValues()
        );
    }
}
