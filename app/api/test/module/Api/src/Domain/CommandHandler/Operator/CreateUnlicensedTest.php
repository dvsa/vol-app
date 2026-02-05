<?php

declare(strict_types=1);

/**
 * Create Unlicensed Operator Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Operator;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Operator\CreateUnlicensed;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as ContactDetailsRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\LicenceNoGen as LicenceNoGenRepo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact as PhoneContactEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Licence\LicenceNoGen as LicenceNoGenEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Transfer\Command\Operator\CreateUnlicensed as CreateUnlicensedCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create Unlicensed Operator Command Handler Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CreateUnlicensedTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateUnlicensed();
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('ContactDetails', ContactDetailsRepo::class);
        $this->mockRepo('LicenceNoGen', LicenceNoGenRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ContactDetailsEntity::CONTACT_TYPE_CORRESPONDENCE_ADDRESS,
            LicenceEntity::LICENCE_TYPE_RESTRICTED,
            LicenceEntity::LICENCE_STATUS_UNLICENSED,
            LicenceEntity::LICENCE_CATEGORY_PSV,
            OrganisationEntity::ORG_TYPE_OTHER,
            PhoneContactEntity::TYPE_PRIMARY,
            PhoneContactEntity::TYPE_SECONDARY,
        ];

        $this->references = [
            TrafficAreaEntity::class => [
                'N' => m::mock(TrafficAreaEntity::class)->makePartial()
                    ->shouldReceive('getIsNi')
                    ->andReturn(true)
                    ->getMock(),
            ],
            ContactDetailsEntity::class => [
                1 => m::mock(ContactDetailsEntity::class)->makePartial(),
            ],
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testHandleCommand(mixed $isExempt, mixed $expectedLicenceNo): void
    {
        $licenceNoGenId = 1234567;

        $contactDetailsData = [
            'emailAddress' => 'foo@bar.com',
            'address' => [
                'addressLine1' => 'al1',
                'addressLine2' => 'al2',
                'addressLine3' => 'al3',
                'addressLine4' => 'al4',
                'town' => 'atown',
                'postcode' => 'pc',
            ],
            'phoneContacts' => [
                [
                    'phoneNumber' => '01234567890',
                    'phoneContactType' => 'phone_t_primary',
                ],
                [
                    'phoneNumber' => '01234567891',
                    'phoneContactType' => 'phone_t_secondary',
                ],
            ]
        ];

        // map ref data references for phone contact types
        $contactDetails = $contactDetailsData;
        $contactDetails['phoneContacts'][0]['phoneContactType'] = $this->mapRefData(
            $contactDetails['phoneContacts'][0]['phoneContactType']
        );
        $contactDetails['phoneContacts'][1]['phoneContactType'] = $this->mapRefData(
            $contactDetails['phoneContacts'][1]['phoneContactType']
        );

        $data = [
            'name' => 'name',
            'operatorType' => 'lcat_psv',
            'trafficArea' => 'N',
            'contactDetails' => $contactDetailsData,
            'isExempt' => $isExempt
        ];

        $command = CreateUnlicensedCmd::create($data);

        $savedLicence = null;

        $this->repoMap['Licence']->shouldReceive('save')
            ->with(m::type(LicenceEntity::class))
            ->andReturnUsing(
                function (LicenceEntity $licence) use (&$savedLicence) {
                    $licence->setId(1);
                    $savedLicence = $licence;
                }
            )
            ->twice(); // saved twice due to licence no. generation

        $this->repoMap['ContactDetails']
            ->shouldReceive('populateRefDataReference')
            ->with($contactDetailsData)
            ->andReturn($contactDetails);

        $savedLicenceNoGen = null;

        $this->repoMap['LicenceNoGen']->shouldReceive('save')
            ->with(m::type(LicenceNoGenEntity::class))
            ->andReturnUsing(
                function (LicenceNoGenEntity $licenceNoGen) use (&$savedLicenceNoGen, $licenceNoGenId) {
                    $licenceNoGen->setId($licenceNoGenId);
                    $savedLicenceNoGen = $licenceNoGen;
                }
            )
            ->once();

        // invoke
        $result = $this->sut->handleCommand($command);

        // assertions...

        // assert response details
        $this->assertEquals(1, $result->getIds()['licence']);
        $this->assertEquals(
            [
                'Licence added',
                'Organisation added',
                'ContactDetails added',
                'Address added',
                'Phone contact(s) added',
            ],
            $result->getMessages()
        );

        // assert organisation record properties
        $this->assertEquals('name', $savedLicence->getOrganisation()->getName());
        $this->assertEquals(OrganisationEntity::ORG_TYPE_OTHER, $savedLicence->getOrganisation()->getType()->getId());
        $this->assertTrue($savedLicence->getOrganisation()->isUnlicensed());

        // assert licence record properties
        $this->assertTrue($savedLicence->isPsv());
        $this->assertEquals(LicenceEntity::LICENCE_TYPE_RESTRICTED, $savedLicence->getLicenceType()->getId());
        $this->assertEquals('N', $savedLicence->getTrafficArea()->getId());
        $this->assertEquals($expectedLicenceNo, $savedLicence->getLicNo());
        $this->assertEquals('Y', $savedLicence->getNiFlag());

        // assert contact details record properties
        $this->assertEquals(
            ContactDetailsEntity::CONTACT_TYPE_CORRESPONDENCE_ADDRESS,
            $savedLicence->getCorrespondenceCd()->getContactType()->getId()
        );
        $this->assertEquals('foo@bar.com', $savedLicence->getCorrespondenceCd()->getEmailAddress());
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
            $savedLicence->getCorrespondenceCd()->getAddress()->toArray()
        );

        $phoneContacts = $savedLicence->getCorrespondenceCd()->getPhoneContacts();
        $this->assertEquals(2, $phoneContacts->count());
        $this->assertEquals('01234567890', $phoneContacts->get(0)->getPhoneNumber());
        $this->assertEquals('phone_t_primary', $phoneContacts->get(0)->getPhoneContactType()->getId());
        $this->assertEquals('01234567891', $phoneContacts->get(1)->getPhoneNumber());
        $this->assertEquals('phone_t_secondary', $phoneContacts->get(1)->getPhoneContactType()->getId());
    }

    public static function dataProvider(): array
    {
        return [
            'notExempt' => [
                'isExempt' => 'N',
                'expectedLicenceNo' => 'UPN1234567'

            ],
            'isExempt' => [
                'isExempt' => 'Y',
                'expectedLicenceNo' => 'EPN1234567'
            ]
        ];
    }
}
