<?php

namespace Dvsa\OlcsTest\Api\Entity\Si;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategory as SiCategoryEntity;
use Dvsa\Olcs\Api\Entity\Si\SiCategoryType as SiCategoryTypeEntity;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Si\SeriousInfringement as Entity;
use Dvsa\Olcs\Api\Entity\Si\SiPenalty as SiPenaltyEntity;
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
    public function testCreate()
    {
        $case = m::mock(CaseEntity::class);
        $checkDate = new \DateTime('2015-12-25');
        $infringementDate = new \DateTime('2015-12-26');
        $siCategory = m::mock(SiCategoryEntity::class);
        $siCategoryType = m::mock(SiCategoryTypeEntity::class);

        $entity = new SeriousInfringement(
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

    /**
     * tests responseSet function
     *
     * @param ArrayCollection $appliedPenalties
     * @param bool $expectedResult
     *
     * @dataProvider responseSetProvider
     */
    public function testResponseSet($appliedPenalties, $expectedResult)
    {
        $entity = m::mock(SeriousInfringement::class)->makePartial();
        $entity->setAppliedPenalties($appliedPenalties);
        $this->assertEquals($expectedResult, $entity->responseSet());
    }

    /**
     * data provide for testResponseSet()
     *
     * @return array
     */
    public function responseSetProvider()
    {
        return [
            [new ArrayCollection(), false],
            [new ArrayCollection([m::mock(SiPenaltyEntity::class)]), true]
        ];
    }

    /**
     * Tests getCalculatedBundleValues
     */
    public function testGetCalculatedBundleValues()
    {
        $entity = m::mock(SeriousInfringement::class)->makePartial();
        $entity->setAppliedPenalties(new ArrayCollection());
        $this->assertEquals(['responseSet' => false], $entity->getCalculatedBundleValues());
    }
}
