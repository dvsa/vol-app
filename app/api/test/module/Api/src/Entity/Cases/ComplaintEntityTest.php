<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Cases;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Cases\Complaint as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Mockery as m;

/**
 * Complaint Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class ComplaintEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * @param string $statusId
     * @param boolean $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isOpenProvider')]
    public function testIsOpen(mixed $statusId, mixed $expected): void
    {
        $sut = $this->instantiate($this->entityClass);

        $status = new RefDataEntity();
        $status->setId($statusId);

        $sut->setStatus($status);

        $this->assertEquals($expected, $sut->isOpen());
    }

    public function testIsOpenWithoutStatus(): void
    {
        $sut = $this->instantiate($this->entityClass);

        $this->assertFalse($sut->isOpen());
    }

    public static function isOpenProvider(): array
    {
        return [
            [Entity::COMPLAIN_STATUS_OPEN, true],
            [Entity::COMPLAIN_STATUS_CLOSED, false],
        ];
    }

    /**
     * @param bool $isCompliance
     * @param bool $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('isEnvironmentalComplaintProvider')]
    public function testIsEnvironmentalComplaint(mixed $isCompliance, mixed $expected): void
    {
        $sut = $this->instantiate($this->entityClass);

        $sut->setIsCompliance($isCompliance);

        $this->assertEquals($expected, $sut->isEnvironmentalComplaint());
    }

    public static function isEnvironmentalComplaintProvider(): array
    {
        return [
            [false, true],
            [true, false],
        ];
    }

    /**
     * @param bool $isCompliance
     * @param string $statusId
     * @param \DateTime|null $closedDate
     * @param \DateTime|null $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('populateClosedDateProvider')]
    public function testPopulateClosedDate(mixed $isCompliance, mixed $statusId, mixed $closedDate, mixed $expected): void
    {
        $sut = $this->instantiate($this->entityClass);

        $sut->setIsCompliance($isCompliance);

        $status = new RefDataEntity();
        $status->setId($statusId);
        $sut->setStatus($status);

        $sut->setClosedDate($closedDate);

        $sut->populateClosedDate();

        $this->assertEquals(
            $expected !== null ? $expected->format('Y-m-d') : null,
            $sut->getClosedDate() !== null ? $sut->getClosedDate()->format('Y-m-d') : null
        );
    }

    public static function populateClosedDateProvider(): array
    {
        return [
            // non-Environmental Complaint
            [true, Entity::COMPLAIN_STATUS_CLOSED, null, null],
            [true, Entity::COMPLAIN_STATUS_OPEN, null, null],

            // Environmental Complaint
            // closed - closed date not set yet
            [false, Entity::COMPLAIN_STATUS_CLOSED, null, new \DateTime()],
            // closed - closed date already set
            [false, Entity::COMPLAIN_STATUS_CLOSED, new \DateTime('2015-02-10'), new \DateTime('2015-02-10')],

            // open - closed date not set yet
            [false, Entity::COMPLAIN_STATUS_OPEN, null, null],
            // open - closed date already set
            [false, Entity::COMPLAIN_STATUS_OPEN, new \DateTime('2015-02-10'), null],
        ];
    }

    /**
     * Test constructor
     */
    public function testConstructor(): void
    {
        $mockCase = m::mock(CasesEntity::class);
        $mockStatus = m::mock(RefDataEntity::class);
        $complaintDate = new \DateTime();
        $mockContactDetails = m::mock(ContactDetailsEntity::class);

        $entity = new Entity($mockCase, true, $mockStatus, $complaintDate, $mockContactDetails);

        $this->assertEquals($mockCase, $entity->getCase());
        $this->assertTrue($entity->getIsCompliance());
        $this->assertInstanceOf(\DateTime::class, $entity->getComplaintDate());
        $this->assertEquals($mockContactDetails, $entity->getComplainantContactDetails());
    }
}
