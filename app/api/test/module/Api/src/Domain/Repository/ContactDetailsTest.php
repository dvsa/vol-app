<?php

declare(strict_types=1);

/**
 * ContactDetails Repo test
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as Repo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;

/**
 * ContactDetails Repo test
 */
class ContactDetailsTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testPopulateRefDataReference(): void
    {
        $data = [
            'address' => ['countryCode' => 'GB'],
            'phoneContacts' => [
                ['phoneContactType' => PhoneContact::TYPE_PRIMARY]
            ],
            'person' => [
                'title' => 'title_miss'
            ]
        ];

        $countryEntity = m::mock(CountryEntity::class);
        $refDataEntity = m::mock(RefDataEntity::class);

        $this->em->shouldReceive('getReference')
            ->once()
            ->with(CountryEntity::class, 'GB')
            ->andReturn($countryEntity);

        $this->em->shouldReceive('getReference')
            ->once()
            ->with(RefDataEntity::class, PhoneContact::TYPE_PRIMARY)
            ->andReturn($refDataEntity);

        $this->em->shouldReceive('getReference')
            ->once()
            ->with(RefDataEntity::class, 'title_miss')
            ->andReturn($refDataEntity);

        $result = $this->sut->populateRefDataReference($data);

        $this->assertEquals(
            [
                'address' => ['countryCode' => $countryEntity],
                'phoneContacts' => [
                    ['phoneContactType' => $refDataEntity]
                ],
                'person' => [
                    'title' => $refDataEntity
                ]
            ],
            $result
        );
    }

    public function testApplyListFiltersLicence(): void
    {
        $sut = m::mock(Repo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $mockDqb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockDqb->shouldReceive('expr->eq')->with('m.contactType', ':contactType')->once()
            ->andReturn('EXPR');
        $mockDqb->shouldReceive('andWhere')->with('EXPR')->once()->andReturnSelf();
        $mockDqb->shouldReceive('setParameter')->with('contactType', 'ct_partner')->once();

        $params = [
            'contactType' => 'ct_partner'
        ];
        $query = \Dvsa\Olcs\Transfer\Query\ContactDetail\ContactDetailsList::create($params);
        $sut->applyListFilters($mockDqb, $query);
    }
}
