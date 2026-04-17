<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Publication;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Publication\PublicationPoliceData as Entity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Mockery as m;

/**
 * PublicationPoliceData Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class PublicationPoliceDataEntityTest extends EntityTester
{
    public function setUp(): void
    {
        /** @var \Dvsa\Olcs\Api\Entity\Publication\PublicationPoliceData entity */
        $this->entity = $this->instantiate($this->entityClass);
    }

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Tests create
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('birthDateProvider')]
    public function testCreate(mixed $birthDate): void
    {
        $publicationLinkMock = m::mock(PublicationLink::class);
        $forename = 'forename';
        $familyName = 'family name';

        $personMock = m::mock(PersonEntity::class);
        $personMock->shouldReceive('getBirthDate')->once()->andReturn($birthDate);
        $personMock->shouldReceive('getForename')->once()->andReturn($forename);
        $personMock->shouldReceive('getFamilyName')->once()->andReturn($familyName);

        $sut = new Entity($publicationLinkMock, $personMock);

        $this->assertEquals($publicationLinkMock, $sut->getPublicationLink());
        $this->assertEquals($personMock, $sut->getPerson());
        $this->assertEquals($birthDate, $sut->getBirthDate());
        $this->assertEquals($forename, $sut->getForename());
        $this->assertEquals($familyName, $sut->getFamilyName());
    }

    public static function birthDateProvider(): array
    {
        return [
            [null],
            [new \DateTime()]
        ];
    }
}
