<?php

declare(strict_types=1);

/**
 * Update Unlicensed Operator Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Operator;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Operator\UpdateUnlicensed;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as ContactDetailsRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Transfer\Command\Operator\UpdateUnlicensed as UpdateUnlicensedCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Update Unlicensed Operator Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UpdateUnlicensedTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateUnlicensed();
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('ContactDetails', ContactDetailsRepo::class);
        $this->mockRepo('Organisation', OrganisationRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ContactDetailsEntity::CONTACT_TYPE_CORRESPONDENCE_ADDRESS,
            LicenceEntity::LICENCE_CATEGORY_PSV,
        ];

        $this->references = [
            OrganisationEntity::class => [
                1 => m::mock(OrganisationEntity::class)->makePartial(),
            ],
            ContactDetailsEntity::class => [
                10 => m::mock(ContactDetailsEntity::class)->makePartial(),
            ],
            LicenceEntity::class => [
                7 => m::mock(LicenceEntity::class)->makePartial(),
            ],
            TrafficAreaEntity::class => [
                'N' => m::mock(TrafficAreaEntity::class)->makePartial()
                ->shouldReceive('getIsNi')
                ->andReturn(true)
                ->getMock(),
            ],
            AddressEntity::class => [
                11 => m::mock(AddressEntity::class)->makePartial(),
            ],
        ];

        parent::initReferences();

        // set up mocked object relations
        $organisation = $this->mapReference(OrganisationEntity::class, 1);
        $licence = $this->mapReference(LicenceEntity::class, 7);
        $correspondenceCd = $this->mapReference(ContactDetailsEntity::class, 10);
        $correspondenceCd->setContactType($this->mapRefData(ContactDetailsEntity::CONTACT_TYPE_CORRESPONDENCE_ADDRESS));
        $correspondenceCd->setAddress($this->mapReference(AddressEntity::class, 11));
        $correspondenceCd->setPhoneContacts(new ArrayCollection());
        $licence->setOrganisation($organisation);
        $organisation->setLicences(new ArrayCollection([$licence]));
        $licence->setCorrespondenceCd($correspondenceCd);
        $licence->setGoodsOrPsv($this->mapRefData(LicenceEntity::LICENCE_CATEGORY_PSV));
        $licence->setTrafficArea($this->mapReference(TrafficAreaEntity::class, 'N'));
    }

    public function testHandleCommand(): void
    {
        $contactDetailsData = [
            'id' => 10,
            'version' => 1,
            'emailAddress' => 'foo@bar.com',
            'address' => [
                'id' => 11,
                'version' => 1,
                'addressLine1' => 'al1',
                'addressLine2' => 'al2',
                'addressLine3' => 'al3',
                'addressLine4' => 'al4',
                'town' => 'atown',
                'postcode' => 'pc',
            ],
        ];

        $data = [
            'id' => 1,
            'version' => 1,
            'name' => 'name',
            'operatorType' => 'lcat_psv',
            'trafficArea' => 'N',
            'contactDetails' => $contactDetailsData,
        ];

        $command = UpdateUnlicensedCmd::create($data);

        $organisation = $this->mapReference(OrganisationEntity::class, 1);
        $this->repoMap['Organisation']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, 1, 1)
            ->andReturn($organisation);

        $licence = $this->mapReference(LicenceEntity::class, 7);
        $this->repoMap['Licence']->shouldReceive('save')
            ->with($licence)
            ->once();

        $this->expectedLicenceCacheClearSideEffect(7);

        $this->repoMap['ContactDetails']
            ->shouldReceive('populateRefDataReference')
            ->with($contactDetailsData)
            ->andReturn($contactDetailsData);

        // invoke
        $result = $this->sut->handleCommand($command);

        // assertions...

        // assert response details
        $this->assertEquals(7, $result->getIds()['licence']);
        $this->assertEquals(1, $result->getIds()['organisation']);
        $this->assertEquals(10, $result->getIds()['contactDetails']);
        $this->assertEquals(['Updated'], $result->getMessages());

        // assert organisation record properties
        $this->assertEquals('name', $licence->getOrganisation()->getName());

        // assert licence record properties
        $this->assertTrue($licence->isPsv());
        $this->assertEquals('N', $licence->getTrafficArea()->getId());
        $this->assertEquals('Y', $licence->getNiFlag());

        // assert contact details record properties
        $this->assertEquals(
            ContactDetailsEntity::CONTACT_TYPE_CORRESPONDENCE_ADDRESS,
            $licence->getCorrespondenceCd()->getContactType()->getId()
        );
        $this->assertEquals('foo@bar.com', $licence->getCorrespondenceCd()->getEmailAddress());
        $this->assertEquals(
            [
                'addressLine1' => 'al1',
                'addressLine2' => 'al2',
                'addressLine3' => 'al3',
                'addressLine4' => 'al4',
                'town' => 'atown',
                'postcode' => 'pc',
                'countryCode' => null,
            ],
            $licence->getCorrespondenceCd()->getAddress()->toArray()
        );
    }
}
