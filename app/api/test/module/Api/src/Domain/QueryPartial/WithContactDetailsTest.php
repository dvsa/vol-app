<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Doctrine\ORM\EntityManagerInterface;
use Dvsa\Olcs\Api\Domain\QueryPartial\WithContactDetails;
use Dvsa\Olcs\Api\Domain\QueryPartial\With;
use Dvsa\Olcs\Api\Domain\QueryPartial\WithRefdata;
use Dvsa\Olcs\Api\Entity\ContactDetails;
use Mockery as m;

/**
 * WithContactDetailsTest
 */
class WithContactDetailsTest extends QueryPartialTestCase
{
    /**
     * @var \Dvsa\Olcs\Api\Domain\QueryPartial\WithRefdata
     */
    public $withRefdata;
    /** @var m\Mock */
    private $em;

    /** @var m\Mock */
    private $with;

    public function setUp(): void
    {
        $this->em = m::mock(EntityManagerInterface::class);
        // Cannot mock With as it is Final
        $this->with = new With();
        // Cannot mock WithRefdata as it is Final
        $this->withRefdata = new WithRefdata($this->em, $this->with);
        $this->sut = new WithContactDetails($this->em, $this->with, $this->withRefdata);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testModifyQuery(mixed $expectedDql, mixed $arguments): void
    {
        $entityMetadata = m::mock();
        $entityMetadata->associationMappings = [
            'property1' => ['targetEntity' => 'Foo'],
        ];
        $this->em->shouldReceive('getClassMetadata')->with(ContactDetails\ContactDetails::class)->once()
            ->andReturn($entityMetadata);
        $this->em->shouldReceive('getClassMetadata')->with(ContactDetails\Address::class)->once()
            ->andReturn($entityMetadata);
        $this->em->shouldReceive('getClassMetadata')->with(ContactDetails\PhoneContact::class)->once()
            ->andReturn($entityMetadata);
        $this->sut->modifyQuery($this->qb, $arguments);
        $this->assertSame(
            $expectedDql,
            $this->qb->getDQL()
        );
    }

    public static function dataProvider(): array
    {
        return [
            [
                'SELECT a, a_cd, a_cd_a, a_cd_a_cc, a_cd_pc FROM foo a LEFT JOIN a.contactDetails a_cd ' .
                    'LEFT JOIN a_cd.address a_cd_a LEFT JOIN a_cd_a.countryCode a_cd_a_cc ' .
                    'LEFT JOIN a_cd.phoneContacts a_cd_pc',
                []
            ],
            [
                'SELECT a, a_cd, a_cd_a, a_cd_a_cc, a_cd_pc FROM foo a LEFT JOIN a.PROP a_cd ' .
                    'LEFT JOIN a_cd.address a_cd_a LEFT JOIN a_cd_a.countryCode a_cd_a_cc ' .
                    'LEFT JOIN a_cd.phoneContacts a_cd_pc',
                ['PROP'],
            ],
            [
                'SELECT a, ALIAS, ALIAS_a, ALIAS_a_cc, ALIAS_pc FROM foo a LEFT JOIN a.PROP ALIAS ' .
                    'LEFT JOIN ALIAS.address ALIAS_a LEFT JOIN ALIAS_a.countryCode ALIAS_a_cc ' .
                    'LEFT JOIN ALIAS.phoneContacts ALIAS_pc',
                ['PROP', 'ALIAS']
            ],
        ];
    }
}
