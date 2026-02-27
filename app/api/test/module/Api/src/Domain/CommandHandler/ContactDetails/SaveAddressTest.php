<?php

declare(strict_types=1);

/**
 * Save Address Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ContactDetails;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\ContactDetails\SaveAddress;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Address as AddressRepo;
use Dvsa\Olcs\Api\Domain\Repository\ContactDetails as ContactDetailsRepo;
use Dvsa\Olcs\Api\Domain\Command\ContactDetails\SaveAddress as Cmd;

/**
 * Save Address Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SaveAddressTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new SaveAddress();
        $this->mockRepo('Address', AddressRepo::class);
        $this->mockRepo('ContactDetails', ContactDetailsRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            ContactDetailsEntity::CONTACT_TYPE_REGISTERED_ADDRESS
        ];

        $this->references = [
            Country::class => [
                'GB' => m::mock(Country::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommandUpdateWithoutChange(): void
    {
        /** @var AddressEntity $address */
        $address = m::mock(AddressEntity::class)->makePartial();
        $address->setVersion(1);
        $address->shouldReceive('updateAddress')
            ->with('address 1', null, null, null, 'Town', 'PostCode', $this->references[Country::class]['GB']);

        $data = [
            'id' => 111,
            'version' => 1,
            'addressLine1' => 'address 1',
            'town' => 'Town',
            'postcode' => 'PostCode',
            'countryCode' => 'GB'
        ];
        $command = Cmd::create($data);

        $this->repoMap['Address']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($address)
            ->shouldReceive('save')
            ->once()
            ->with($address);

        $result = $this->sut->handleCommand($command);

        $this->assertFalse($result->getFlag('hasChanged'));

        $expected = [
            'id' => [],
            'messages' => [
                'Address unchanged'
            ],
            'flags' => ['hasChanged' => null]

        ];

        $this->assertEquals($expected, $result->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('countryProvider')]
    public function testHandleCommandUpdateWithChange(mixed $countryCode): void
    {
        if ($countryCode === '') {
            $expectedCountryCode = null;
        } else {
            $expectedCountryCode = $this->references[Country::class]['GB'];
        }
        /** @var AddressEntity $address */
        $address = m::mock(AddressEntity::class)->makePartial();
        $address->shouldReceive('updateAddress')
            ->with('address 1', null, null, null, 'Town', 'PostCode', $expectedCountryCode);

        $data = [
            'id' => 111,
            'version' => 1,
            'addressLine1' => 'address 1',
            'town' => 'Town',
            'postcode' => 'PostCode',
            'countryCode' => $countryCode
        ];
        $command = Cmd::create($data);

        $this->repoMap['Address']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($address)
            ->shouldReceive('save')
            ->once()
            ->with($address)
            ->andReturnUsing(
                function (AddressEntity $address) {
                    $address->setVersion(2);
                }
            );

        $result = $this->sut->handleCommand($command);

        $this->assertTrue($result->getFlag('hasChanged'));

        $expected = [
            'id' => [],
            'messages' => [
                'Address updated'
            ],
            'flags' => ['hasChanged' => 1]
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    public static function countryProvider(): array
    {
        return [
            [''],
            ['GB']
        ];
    }

    public function testHandleCommandCreate(): void
    {
        $data = [
            'addressLine1' => 'address 1',
            'town' => 'Town',
            'postcode' => 'PostCode',
            'countryCode' => 'GB',
            'contactType' => ContactDetailsEntity::CONTACT_TYPE_REGISTERED_ADDRESS
        ];
        $command = Cmd::create($data);

        /** @var AddressEntity $savedAddress */
        $savedAddress = null;
        /** @var ContactDetailsEntity $savedContactDetails */
        $savedContactDetails = null;

        $this->repoMap['Address']->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (AddressEntity $address) use (&$savedAddress) {
                    $address->setId(111);
                    $savedAddress = $address;
                }
            );

        $this->repoMap['ContactDetails']->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (ContactDetailsEntity $contactDetails) use (&$savedContactDetails) {
                    $contactDetails->setId(222);
                    $savedContactDetails = $contactDetails;
                }
            );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(AddressEntity::class, $savedAddress);
        $this->assertInstanceOf(ContactDetailsEntity::class, $savedContactDetails);

        $this->assertSame($savedAddress, $savedContactDetails->getAddress());
        $this->assertSame(
            $this->refData[ContactDetailsEntity::CONTACT_TYPE_REGISTERED_ADDRESS],
            $savedContactDetails->getContactType()
        );

        $expected = [
            'id' => [
                'address' => 111,
                'contactDetails' => 222
            ],
            'messages' => [
                'Contact Details created',
                'Address created'
            ],
            'flags' => ['hasChanged' => 1]
        ];

        $this->assertTrue($result->getFlag('hasChanged'));

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandCreateNoContactDetailsType(): void
    {
        $data = [
            'addressLine1' => 'address 1',
            'town' => 'Town',
            'postcode' => 'PostCode',
            'countryCode' => 'GB'
        ];
        $command = Cmd::create($data);

        /** @var AddressEntity $savedAddress */
        $savedAddress = null;

        $this->repoMap['Address']->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (AddressEntity $address) use (&$savedAddress) {
                    $address->setId(111);
                    $savedAddress = $address;
                }
            );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(AddressEntity::class, $savedAddress);

        $expected = [
            'id' => [
                'address' => 111
            ],
            'messages' => [
                'Address created'
            ],
            'flags' => ['hasChanged' => 1]
        ];

        $this->assertTrue($result->getFlag('hasChanged'));

        $this->assertEquals($expected, $result->toArray());
    }
}
