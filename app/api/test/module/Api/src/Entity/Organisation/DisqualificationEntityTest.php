<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Organisation;

use Mockery as m;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Organisation\Disqualification as Entity;

/**
 * Disqualification Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class DisqualificationEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * @var Entity
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Entity(m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class));

        parent::setUp();
    }

    public function testConstructorNoParams(): void
    {
        try {
            $sut = new Entity();
            $this->assertInstanceOf(Entity::class, $sut);
            $this->fail();
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertArrayHasKey('DISQ_MISSING_ORG_OFFICER', $e->getMessages());
        }
    }

    public function testConstructorBothParams(): void
    {
        try {
            $sut = new Entity(
                m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class),
                m::mock(\Dvsa\Olcs\Api\Entity\Person\Person::class)
            );
            $this->assertInstanceOf(Entity::class, $sut);
            $this->fail();
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertArrayHasKey('DISQ_BOTH_ORG_OFFICER', $e->getMessages());
        }
    }

    public function testConstructorOrganisation(): void
    {
        $organisation = m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class);
        $sut = new Entity($organisation);
        $this->assertSame($organisation, $sut->getOrganisation());
    }

    public function testConstructorPerson(): void
    {
        $person = m::mock(\Dvsa\Olcs\Api\Entity\Person\Person::class);
        $sut = new Entity(null, $person);
        $this->assertSame($person, $sut->getPerson());
    }

    public function testUpdateMinimumParams(): void
    {
        $this->sut->update(
            'N'
        );
        $this->assertSame('N', $this->sut->getIsDisqualified());
        $this->assertSame(null, $this->sut->getStartDate());
        $this->assertSame(null, $this->sut->getNotes());
        $this->assertSame(null, $this->sut->getPeriod());
    }

    public function testUpdateAllParams(): void
    {
        $startDate = new \Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime('2014-12-02');
        $this->sut->update(
            'Y',
            $startDate,
            'NOTES',
            41
        );
        $this->assertSame('Y', $this->sut->getIsDisqualified());
        $this->assertSame($startDate, $this->sut->getStartDate());
        $this->assertSame('NOTES', $this->sut->getNotes());
        $this->assertSame(41, $this->sut->getPeriod());
    }

    public function testUpdateValidationStartDate(): void
    {
        try {
            $this->sut->update(
                'Y',
                null,
                'NOTES',
                41
            );
            $this->fail();
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertArrayHasKey('DISQ_START_DATE_MISSING', $e->getMessages());
        }
    }

    public function testGetStatusN(): void
    {
        $this->sut->setStartDate((new \Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime('-1 month'))->format('Y-m-d'));
        $this->sut->setPeriod(12);
        $this->sut->setIsDisqualified('N');

        $this->assertSame(Entity::STATUS_INACTIVE, $this->sut->getStatus());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetStatus')]
    public function testGetStatus(mixed $expectedStatus, mixed $startDate, mixed $period): void
    {
        $this->sut->setStartDate((new \Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime($startDate))->format('Y-m-d'));
        $this->sut->setPeriod($period);
        $this->sut->setIsDisqualified('Y');

        $this->assertSame($expectedStatus, $this->sut->getStatus());
    }

    public static function dpGetStatus(): array
    {
        return [
            [Entity::STATUS_INACTIVE, '-2 month', 1],
            [Entity::STATUS_INACTIVE, '-12 month', 11],
            [Entity::STATUS_INACTIVE, '-1 year', 11],
            [Entity::STATUS_INACTIVE, '+1 day', null],
            [Entity::STATUS_INACTIVE, '+1 day', 0],
            [Entity::STATUS_ACTIVE, '-1 month', 3],
            [Entity::STATUS_ACTIVE, '-1 month', null],
            [Entity::STATUS_ACTIVE, '-1 month', 0],
            [Entity::STATUS_ACTIVE, '-1 year', null],
            [Entity::STATUS_ACTIVE, '-1 year', 0],
            [Entity::STATUS_ACTIVE, '', 0],
            [Entity::STATUS_ACTIVE, '', 1],
        ];
    }

    public function testGetCalculatedBundleValues(): void
    {
        $this->assertSame(
            [
                'endDate' => null,
                'status' => 'Inactive'
            ],
            $this->sut->getCalculatedBundleValues()
        );
    }
}
