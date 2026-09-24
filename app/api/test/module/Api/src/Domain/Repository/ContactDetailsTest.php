<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as Repo;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as Entity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Transfer\Query\ContactDetail\ContactDetailsList;
use Mockery as m;

final class ContactDetailsTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
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

        $this->em->expects('getReference')->with(CountryEntity::class, 'GB')->andReturn($countryEntity);
        $this->em->expects('getReference')
            ->with(RefDataEntity::class, PhoneContact::TYPE_PRIMARY)
            ->andReturn($refDataEntity);
        $this->em->expects('getReference')->with(RefDataEntity::class, 'title_miss')->andReturn($refDataEntity);

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
            $this->sut->populateRefDataReference($data),
        );
    }

    public function testApplyListFiltersLicence(): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListFilters($qb, ContactDetailsList::create(['contactType' => 'ct_partner']));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.contactType = :contactType',
            $qb->getDQL(),
        );
        $this->assertSame('ct_partner', $qb->getParameter('contactType')->getValue());
    }
}
