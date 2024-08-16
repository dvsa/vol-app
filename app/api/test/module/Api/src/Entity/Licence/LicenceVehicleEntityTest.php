<?php

namespace Dvsa\OlcsTest\Api\Entity\Licence;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Mockery as m;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Licence\LicenceVehicle as Entity;

/**
 * LicenceVehicle Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class LicenceVehicleEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetActiveDiscWithout()
    {
        /** @var Entity $entity */
        $entity = $this->instantiate(Entity::class);

        $goodsDiscs = new ArrayCollection();

        $entity->setGoodsDiscs($goodsDiscs);

        $this->assertNull($entity->getActiveDisc());
    }

    public function testGetActiveDisc()
    {
        /** @var Entity $entity */
        $entity = $this->instantiate(Entity::class);

        /** @var GoodsDisc $goodsDisc */
        $goodsDisc1 = m::mock(GoodsDisc::class)->makePartial();
        $goodsDisc1->setCeasedDate(new DateTime());

        /** @var GoodsDisc $goodsDisc */
        $goodsDisc2 = m::mock(GoodsDisc::class)->makePartial();

        $goodsDiscs = new ArrayCollection();
        $goodsDiscs->add($goodsDisc1);
        $goodsDiscs->add($goodsDisc2);

        $entity->setGoodsDiscs($goodsDiscs);

        $this->assertSame($goodsDisc2, $entity->getActiveDisc());
    }

    public function testGetActiveDiscCeased()
    {
        /** @var Entity $entity */
        $entity = $this->instantiate(Entity::class);

        /** @var GoodsDisc $goodsDisc */
        $goodsDisc = m::mock(GoodsDisc::class)->makePartial();
        $goodsDisc->setCeasedDate(new DateTime());

        $goodsDiscs = new ArrayCollection();
        $goodsDiscs->add($goodsDisc);

        $entity->setGoodsDiscs($goodsDiscs);

        $this->assertNull($entity->getActiveDisc());
    }

    public function testConstruct()
    {
        $licence = m::mock(Licence::class);
        $vehicle = m::mock(Vehicle::class);

        $entity = new Entity($licence, $vehicle);

        $this->assertSame($licence, $entity->getLicence());
        $this->assertSame($vehicle, $entity->getVehicle());
    }

    public function testMarkAsDuplicate()
    {
        /** @var Entity $entity */
        $entity = $this->instantiate(Entity::class);

        $entity->setWarningLetterSentDate(new DateTime());

        $entity->markAsDuplicate();

        $now = new DateTime();

        $this->assertEquals($now->format('Y-m-d'), $entity->getWarningLetterSeedDate()->format('Y-m-d'));

        $this->assertNull($entity->getWarningLetterSentDate());
    }

    public function testUpdateDuplicateMarkWithPendingLetter()
    {
        /** @var Entity $entity */
        $entity = $this->instantiate(Entity::class);

        $entity->setWarningLetterSeedDate(new DateTime('2015-01-01'));

        $entity->updateDuplicateMark();

        $this->assertEquals('2015-01-01', $entity->getWarningLetterSeedDate()->format('Y-m-d'));
    }

    public function testUpdateDuplicateMarkWithSentLetter()
    {
        /** @var Entity $entity */
        $entity = $this->instantiate(Entity::class);

        $entity->setWarningLetterSeedDate(new DateTime('2015-01-01'));
        $entity->setWarningLetterSentDate(new DateTime('2015-01-29'));

        $entity->updateDuplicateMark();

        $today = new DateTime();

        $this->assertEquals($today->format('Y-m-d'), $entity->getWarningLetterSeedDate()->format('Y-m-d'));
        $this->assertNull($entity->getWarningLetterSentDate());
    }

    public function testRemoveDuplicateMark()
    {
        /** @var Entity $entity */
        $entity = $this->instantiate(Entity::class);

        $entity->setWarningLetterSeedDate(new DateTime('2015-01-01'));
        $entity->setWarningLetterSentDate(new DateTime('2015-01-01'));

        $entity->removeDuplicateMark(true);

        $this->assertNull($entity->getWarningLetterSeedDate());
        $this->assertNull($entity->getWarningLetterSentDate());
    }

    public function testGetRelatedOrganisation()
    {
        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getRelatedOrganisation')->with()->once()->andReturn('ORG');

        /** @var Entity $entity */
        $entity = $this->instantiate(Entity::class);
        $entity->setLicence($licence);

        $this->assertSame('ORG', $entity->getRelatedOrganisation());
    }
}
