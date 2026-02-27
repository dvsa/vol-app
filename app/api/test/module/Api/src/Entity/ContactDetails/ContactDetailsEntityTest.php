<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\ContactDetails;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as Entity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * ContactDetails Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class ContactDetailsEntityTest extends EntityTester
{
    public const DEF_ADDRESS_ID = 8888;
    public const DEF_PHONE_ID = 9999;
    public const DEF_PHONE_NR = 'unit_PhoneNr';
    public const DEF_PHONE_TYPE = PhoneContact::TYPE_PRIMARY;
    public const DEF_COUNTRY_CODE = 'unit_CountryCode';
    public const DEF_PERSON_ID = 77777;

    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstruct(): void
    {
        $contactType = m::mock(RefData::class);

        $entity = new ContactDetails($contactType);

        $this->assertSame($contactType, $entity->getContactType());
    }

    public function testUpdateContactDetailsWithPersonAndEmailAddress(): void
    {
        $contactType = m::mock(RefData::class);
        $entity = new ContactDetails($contactType);
        $person = m::mock(PersonEntity::class);

        $entity->updateContactDetailsWithPersonAndEmailAddress($person, 'email@address.com');
        $this->assertSame($person, $entity->getPerson());
        $this->assertEquals('email@address.com', $entity->getEmailAddress());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestCreate')]
    public function testCreate(mixed $contactType, array $data = [], array $expect = []): void
    {
        $cdTypeEntity = (new RefData())->setId($contactType);

        //  call
        $sut = ContactDetails::create($cdTypeEntity, $data);

        //  check
        static::assertSame($cdTypeEntity, $sut->getContactType());

        if (isset($expect['address'])) {
            static::assertEquals($expect['address'], $sut->getAddress());
        } else {
            static::assertNull($sut->getAddress());
        }

        static::assertEquals($expect['email'] ?? null, $sut->getEmailAddress());
        static::assertEquals($expect['desc'] ?? null, $sut->getDescription());

        if (isset($expect['person'])) {
            static::assertEquals($expect['person'], $sut->getPerson());
        } else {
            static::assertNull($sut->getPerson());
        }

        $phoneContacts = $sut->getPhoneContacts();
        static::assertInstanceOf(ArrayCollection::class, $phoneContacts);
        static::assertEmpty($phoneContacts);
    }

    public static function dpTestCreate(): array
    {
        return [
            [
                'contactType' => ContactDetails::CONTACT_TYPE_IRFO_OPERATOR,
            ],
            [
                'contactType' => ContactDetails::CONTACT_TYPE_PARTNER,
                'data' => [
                    'description' => null,
                    'address' => [
                        'addressLine1' => null,
                        'addressLine2' => null,
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => null,
                        'postcode' => null,
                        'countryCode' => null,
                    ],
                ],
                'expect' => [
                    'desc' => null,
                    'address' => new Address(),
                ],
            ],
            [
                'contactType' => ContactDetails::CONTACT_TYPE_OBJECTOR,
            ],
            [
                'contactType' => ContactDetails::CONTACT_TYPE_STATEMENT_REQUESTOR,
                'data' => [
                    'person' => [
                        'title' => null,
                        'forename' => null,
                        'familyName' => null,
                        'birthDate' => null,
                    ],
                    'address' => [
                        'addressLine1' => null,
                        'addressLine2' => null,
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => null,
                        'postcode' => null,
                        'countryCode' => null,
                    ],
                ],
                'expect' => [
                    'person' => new Person(),
                    'address' => new Address(),
                ],
            ],
            [
                'contactType' => ContactDetails::CONTACT_TYPE_USER,
                'data' => [
                    'emailAddress' => null,
                    'person' => [
                        'title' => null,
                        'forename' => null,
                        'familyName' => null,
                        'birthDate' => null,
                    ],
                ],
                'expect' => [
                    'email' => null,
                    'person' => new Person(),
                ],
            ],
            [
                'contactType' => ContactDetails::CONTACT_TYPE_COMPLAINANT,
                'data' => [
                    'person' => [
                        'title' => null,
                        'forename' => null,
                        'familyName' => null,
                        'birthDate' => null,
                    ],
                    'address' => [
                        'addressLine1' => null,
                        'addressLine2' => null,
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => null,
                        'postcode' => null,
                        'countryCode' => null,
                    ],
                ],
                'expect' => [
                    'person' => new Person(),
                    'address' => new Address(),
                ],
            ],
            [
                'contactType' => ContactDetails::CONTACT_TYPE_CORRESPONDENCE_ADDRESS,
            ],
        ];
    }

    /**
     * make sure only tm email address is updated and other details remain the same
     */
    public function testTmUserUpdateSelfserve(): void
    {
        $emailAddress = 'email@email.com';
        $contactParams = [
            'emailAddress' => $emailAddress,
        ];

        $contactType = m::mock(RefData::class);
        $contactType->expects('getId')->withNoArgs()->andReturn(Entity::CONTACT_TYPE_TRANSPORT_MANAGER);

        $sut = new ContactDetails($contactType);

        $address = m::mock(Address::class);
        $sut->setAddress($address);

        $person = m::mock(Person::class);
        $sut->setPerson($person);

        $phoneContacts = m::mock(ArrayCollection::class);
        $sut->setPhoneContacts($phoneContacts);

        $sut->update($contactParams, false);

        static::assertEquals($emailAddress, $sut->getEmailAddress());
        static::assertEquals($address, $sut->getAddress());
        static::assertEquals($person, $sut->getPerson());
        static::assertEquals($phoneContacts, $sut->getPhoneContacts());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestUpdate')]
    public function testUpdate(mixed $contactType, mixed $data, mixed $expect, mixed $fromInternal): void
    {
        $cdTypeEntity = (new RefData())->setId($contactType);

        $sut = new ContactDetails($cdTypeEntity);
        $sut->setAddress((new Address())->setId(self::DEF_ADDRESS_ID));
        $sut->setPerson((new Person())->setId(self::DEF_PERSON_ID));

        $mockPhoneCollection = new ArrayCollection();
        $mockPhoneCollection->offsetSet(
            self::DEF_PHONE_ID,
            (new PhoneContact(new RefData(self::DEF_PHONE_TYPE)))
                ->setId(self::DEF_PHONE_ID)
                ->setPhoneNumber(self::DEF_PHONE_NR)
        );

        $sut->setPhoneContacts($mockPhoneCollection);

        // update the entity
        $sut->update($data, $fromInternal);

        //  check
        static::assertSame($cdTypeEntity, $sut->getContactType());

        $checkPhones = [];
        /** @var PhoneContact $phone */
        foreach ($sut->getPhoneContacts() as $phone) {
            $checkPhones[] = [
                'id' => $phone->getId(),
                'type' => $phone->getPhoneContactType()->getId(),
                'number' => $phone->getPhoneNumber(),
            ];
        }

        $person = $sut->getPerson();

        static::assertEquals(self::DEF_ADDRESS_ID, $sut->getAddress()->getId());
        static::assertEquals(self::DEF_PERSON_ID, $person->getId());

        static::assertEquals(
            $expect,
            array_filter(
                [
                    'desc' => $sut->getDescription(),
                    'email' => $sut->getEmailAddress(),
                    'address' => $sut->getAddress()->toArray(),
                    'person' => array_filter(
                        [
                            'title' => $person->getTitle(),
                            'fullName' => $person->getFullName(),
                            'dob' => ($person->getBirthDate() ? $person->getBirthDate()->format('Y-m-d') : null),
                        ]
                    ),
                    'phones' => $checkPhones,
                ]
            )
        );
    }

    public static function dpTestUpdate(): array
    {
        return [
            //  test CONTACT_TYPE_IRFO_OPERATOR
            [
                'contactType' => ContactDetails::CONTACT_TYPE_IRFO_OPERATOR,
                'data' => [
                    'emailAddress' => 'unit_Email',
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => 'unit_Addr3',
                        'addressLine4' => 'unit_Addr4',
                        'town' => 'unit_Town',
                        'postcode' => 'unit_PostCode',
                        'countryCode' => (new Country())->setId('Unit_Other_CountryCode'),
                    ],
                    'phoneContacts' => [
                        [
                            'id' => null,
                            'phoneContactType' => new RefData('unit_PhoneContactType1'),
                            'phoneNumber' => 'unit_Phone1',
                        ],
                        [
                            'id' => self::DEF_PHONE_ID,
                            'phoneContactType' => new RefData('unit_Other_PhoneContactType'),
                            'phoneNumber' => 'unit_Phone2',
                        ],
                        [
                            'phoneContactType' => new RefData('unit_PhoneContactType2'),
                            'phoneNumber' => '',
                        ],
                    ],
                ],
                'expect' => [
                    'email' => 'unit_Email',
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => 'unit_Addr3',
                        'addressLine4' => 'unit_Addr4',
                        'town' => 'unit_Town',
                        'postcode' => 'unit_PostCode',
                        'countryCode' => 'Unit_Other_CountryCode',
                    ],
                    'phones' => [
                        [
                            'id' => self::DEF_PHONE_ID,
                            'type' => self::DEF_PHONE_TYPE,
                            'number' => 'unit_Phone2',
                        ],
                        [
                            'id' => null,
                            'type' => 'unit_PhoneContactType1',
                            'number' => 'unit_Phone1',
                        ],
                    ],
                ],
                "fromInternal" => false
            ],
            //  test update of CONTACT_TYPE_PARTNER
            [
                'contactType' => ContactDetails::CONTACT_TYPE_PARTNER,
                'data' => [
                    'description' => 'unit_Desc',
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => 'unit_Addr3',
                        'addressLine4' => 'unit_Addr4',
                        'town' => 'unit_Town',
                        'postcode' => 'unit_PostCode',
                        'countryCode' => null,
                    ],
                ],
                'expect' => [
                    'desc' => 'unit_Desc',
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => 'unit_Addr3',
                        'addressLine4' => 'unit_Addr4',
                        'town' => 'unit_Town',
                        'postcode' => 'unit_PostCode',
                        'countryCode' => null,
                    ],
                    'phones' => [
                        [
                            'id' => self::DEF_PHONE_ID,
                            'type' => self::DEF_PHONE_TYPE,
                            'number' => self::DEF_PHONE_NR,
                        ],
                    ],
                ],
                "fromInternal" => false
            ],
            //  test update of CONTACT_TYPE_OBJECTOR
            [
                'contactType' => ContactDetails::CONTACT_TYPE_OBJECTOR,
                'data' => [
                    'emailAddress' => 'unit_Email',
                    'description' => 'unit_Desc',
                    'person' => [
                        'title' => null,
                        'forename' => 'unit_ForeName',
                        'familyName' => 'unit_FamilyName',
                        'birthDate' => '1976-05-04',
                    ],
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => null,
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => 'unit_Town',
                        'postcode' => 'unit_PostCode',
                        'countryCode' => (new Country())->setId(self::DEF_COUNTRY_CODE),
                    ],
                    'phoneContacts' => [
                        [
                            'id' => self::DEF_PHONE_ID,
                            'phoneContactType' => new RefData('unit_Other_PhoneContactType'),
                            'phoneNumber' => 'unit_PhoneNumber',
                        ],
                    ],
                ],
                'expect' => [
                    'email' => 'unit_Email',
                    'desc' => 'unit_Desc',
                    'person' => [
                        'fullName' => 'unit_ForeName unit_FamilyName',
                        'dob' => '1976-05-04',
                    ],
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => null,
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => 'unit_Town',
                        'postcode' => 'unit_PostCode',
                        'countryCode' => self::DEF_COUNTRY_CODE,
                    ],
                    'phones' => [
                        [
                            'id' => self::DEF_PHONE_ID,
                            'type' => self::DEF_PHONE_TYPE,
                            'number' => 'unit_PhoneNumber',
                        ],
                    ],
                ],
                "fromInternal" => false
            ],
            //  test update of CONTACT_TYPE_STATEMENT_REQUESTOR
            [
                'contactType' => ContactDetails::CONTACT_TYPE_STATEMENT_REQUESTOR,
                'data' => [
                    'person' => [
                        'title' => new RefData('unit_Title'),
                        'forename' => null,
                        'familyName' => 'unit_FamilyName',
                        'birthDate' => '1977-06-05',
                    ],
                    'address' => [
                        'addressLine1' => null,
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => null,
                        'postcode' => 'unit_PostCode',
                        'countryCode' => null,
                    ],
                ],
                'expect' => [
                    'person' => [
                        'title' => 'unit_Title',
                        'fullName' => 'unit_FamilyName',
                        'dob' => '1977-06-05',
                    ],
                    'address' => [
                        'addressLine1' => null,
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => null,
                        'postcode' => 'unit_PostCode',
                        'countryCode' => null,
                    ],
                    'phones' => [
                        [
                            'id' => self::DEF_PHONE_ID,
                            'type' => self::DEF_PHONE_TYPE,
                            'number' => self::DEF_PHONE_NR,
                        ],
                    ],
                ],
                "fromInternal" => false
            ],
            //  test update of CONTACT_TYPE_USER FROM SelfServe
            [
                'contactType' => ContactDetails::CONTACT_TYPE_USER,
                'data' => [
                    'emailAddress' => 'unit_Email',
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => null,
                        'postcode' => 'unit_PostCode',
                        'countryCode' => (new Country())->setId('unit_CountryCodeUser'),
                    ],
                    'phoneContacts' => [
                        [
                            'id' => null,
                            'phoneContactType' => new RefData('unit_PhoneContactType1'),
                            'phoneNumber' => 'unit_Phone1',
                        ],
                        [
                            'id' => self::DEF_PHONE_ID,
                            'phoneContactType' => new RefData('unit_Other_PhoneContactType'),
                            'phoneNumber' => '',
                        ],
                    ],
                ],
                'expect' => [
                    'email' => 'unit_Email',
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => null,
                        'postcode' => 'unit_PostCode',
                        'countryCode' => 'unit_CountryCodeUser',
                    ],
                    'phones' => [
                        [
                            'id' => null,
                            'type' => 'unit_PhoneContactType1',
                            'number' => 'unit_Phone1',
                        ],
                    ],
                ],
                "fromInternal" => false
            ],
            [
                'contactType' => ContactDetails::CONTACT_TYPE_USER,
                'data' => [
                    'emailAddress' => 'unit_Email',
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => null,
                        'postcode' => 'unit_PostCode',
                        'countryCode' => (new Country())->setId('unit_CountryCodeUser'),
                    ],
                    'phoneContacts' => [
                        [
                            'id' => null,
                            'phoneContactType' => new RefData('unit_PhoneContactType1'),
                            'phoneNumber' => 'unit_Phone1',
                        ],
                        [
                            'id' => self::DEF_PHONE_ID,
                            'phoneContactType' => new RefData('unit_Other_PhoneContactType'),
                            'phoneNumber' => '',
                        ],
                    ],
                ],
                'expect' => [
                    'email' => 'unit_Email',
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => null,
                        'postcode' => 'unit_PostCode',
                        'countryCode' => 'unit_CountryCodeUser',
                    ],
                    'phones' => [
                        [
                            'id' => null,
                            'type' => 'unit_PhoneContactType1',
                            'number' => 'unit_Phone1',
                        ],
                    ],
                ],
                "fromInternal" => true
            ],
            'TM from internal' => [
                'contactType' => ContactDetails::CONTACT_TYPE_TRANSPORT_MANAGER,
                'data' => [
                    'emailAddress' => 'unit_Email',
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => null,
                        'postcode' => 'unit_PostCode',
                        'countryCode' => (new Country())->setId('unit_CountryCodeUser'),
                    ],
                    'phoneContacts' => [
                        [
                            'id' => null,
                            'phoneContactType' => new RefData('unit_PhoneContactType1'),
                            'phoneNumber' => 'unit_Phone1',
                        ],
                        [
                            'id' => self::DEF_PHONE_ID,
                            'phoneContactType' => new RefData('unit_Other_PhoneContactType'),
                            'phoneNumber' => '',
                        ],
                    ],
                ],
                'expect' => [
                    'email' => 'unit_Email',
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => null,
                        'addressLine4' => null,
                        'town' => null,
                        'postcode' => 'unit_PostCode',
                        'countryCode' => 'unit_CountryCodeUser',
                    ],
                    'phones' => [
                        [
                            'id' => null,
                            'type' => 'unit_PhoneContactType1',
                            'number' => 'unit_Phone1',
                        ],
                    ],
                ],
                "fromInternal" => true
            ],
            //  test update of CONTACT_TYPE_COMPLAINANT
            [
                'contactType' => ContactDetails::CONTACT_TYPE_COMPLAINANT,
                'data' => [
                    'person' => [
                        'title' => new RefData('unit_PersonTitle'),
                        'forename' => 'unit_ForeName',
                        'familyName' => 'unit_FamilyName',
                        'birthDate' => '1978-07-06',
                    ],
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => 'unit_Addr3',
                        'addressLine4' => 'unit_Addr4',
                        'town' => 'unit_Town',
                        'postcode' => 'unit_PostCode',
                        'countryCode' => (new Country())->setId(self::DEF_COUNTRY_CODE),
                    ],
                ],
                'expect' => [
                    'person' => [
                        'title' => 'unit_PersonTitle',
                        'fullName' => 'unit_ForeName unit_FamilyName',
                        'dob' => '1978-07-06',
                    ],
                    'address' => [
                        'addressLine1' => 'unit_Addr1',
                        'addressLine2' => 'unit_Addr2',
                        'addressLine3' => 'unit_Addr3',
                        'addressLine4' => 'unit_Addr4',
                        'town' => 'unit_Town',
                        'postcode' => 'unit_PostCode',
                        'countryCode' => self::DEF_COUNTRY_CODE,
                    ],
                    'phones' => [
                        [
                            'id' => self::DEF_PHONE_ID,
                            'type' => self::DEF_PHONE_TYPE,
                            'number' => self::DEF_PHONE_NR,
                        ],
                    ],
                ],
                "fromInternal" => false
            ],
            //  test update of CONTACT_TYPE_
            [
                'contactType' => ContactDetails::CONTACT_TYPE_CORRESPONDENCE_ADDRESS,
                'data' => [
                    'emailAddress' => 'unit_Email',
                    'address' => [
                        'addressLine1' => null,
                        'addressLine2' => null,
                        'addressLine3' => null,
                        'addressLine4' => 'unit_Addr4',
                        'town' => null,
                        'postcode' => 'unit_PostCode',
                        'countryCode' => (new Country())->setId(self::DEF_COUNTRY_CODE),
                    ],
                    'phoneContacts' => [
                        [
                            'id' => self::DEF_PHONE_ID,
                            'phoneContactType' => new RefData('unit_PhoneContactType1'),
                            'phoneNumber' => 'unit_Phone1',
                        ],
                        [
                            'id' => null,
                            'phoneContactType' => new RefData('unit_PhoneContactType2'),
                            'phoneNumber' => 'unit_Phone2',
                        ],
                    ],
                ],
                'expect' => [
                    'email' => 'unit_Email',
                    'address' => [
                        'addressLine1' => null,
                        'addressLine2' => null,
                        'addressLine3' => null,
                        'addressLine4' => 'unit_Addr4',
                        'town' => null,
                        'postcode' => 'unit_PostCode',
                        'countryCode' => self::DEF_COUNTRY_CODE,
                    ],
                    'phones' => [
                        [
                            'id' => self::DEF_PHONE_ID,
                            'type' => self::DEF_PHONE_TYPE,
                            'number' => 'unit_Phone1',
                        ],
                        [
                            'id' => null,
                            'type' => 'unit_PhoneContactType2',
                            'number' => 'unit_Phone2',
                        ],
                    ],
                ],
                "fromInternal" => false
            ],
        ];
    }

    public function testGetPhoneContactNumber(): void
    {
        $sut = new ContactDetails(m::mock(RefData::class));

        $mockPhoneCollection = new ArrayCollection();
        $mockPhoneCollection->offsetSet(
            self::DEF_PHONE_ID,
            (new PhoneContact(new RefData(self::DEF_PHONE_TYPE)))
                ->setId(self::DEF_PHONE_ID)
                ->setPhoneNumber(self::DEF_PHONE_NR)
        );
        $sut->setPhoneContacts($mockPhoneCollection);

        $this->assertEquals(self::DEF_PHONE_NR, $sut->getPhoneContactNumber(self::DEF_PHONE_TYPE));
    }

    public function testGetPhoneContactNumberEmpty(): void
    {
        $sut = new ContactDetails(m::mock(RefData::class));

        $mockPhoneCollection = new ArrayCollection();
        $sut->setPhoneContacts($mockPhoneCollection);

        $this->assertNull($sut->getPhoneContactNumber('foo'));
    }
}
