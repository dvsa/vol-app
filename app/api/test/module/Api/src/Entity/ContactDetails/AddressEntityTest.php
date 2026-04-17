<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\ContactDetails;

use Mockery as m;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as Entity;

/**
 * Address Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class AddressEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testUpdateAddress(): void
    {
        $address = new Address();
        $country = m::mock(Country::class);

        $address->updateAddress(
            'address 1',
            'address 2',
            'address 3',
            'address 4',
            'town',
            'postcode',
            $country
        );

        $this->assertEquals('address 1', $address->getAddressLine1());
        $this->assertEquals('address 2', $address->getAddressLine2());
        $this->assertEquals('address 3', $address->getAddressLine3());
        $this->assertEquals('address 4', $address->getAddressLine4());
        $this->assertEquals('town', $address->getTown());
        $this->assertEquals('postcode', $address->getPostcode());
        $this->assertSame($country, $address->getCountryCode());

        $address->updateAddress(
            'address 11',
            'address 22',
            'address 33',
            'address 44',
            'town 1',
            'postcode1'
        );

        $this->assertEquals('address 11', $address->getAddressLine1());
        $this->assertEquals('address 22', $address->getAddressLine2());
        $this->assertEquals('address 33', $address->getAddressLine3());
        $this->assertEquals('address 44', $address->getAddressLine4());
        $this->assertEquals('town 1', $address->getTown());
        $this->assertEquals('postcode1', $address->getPostcode());
        $this->assertNull($address->getCountryCode());
    }

    public function testToArray(): void
    {
        $country = new Country();
        $country->setId(9999);

        $sut = new Address();

        // check with country code
        $sut->updateAddress('address 1', 'address 2', 'address 3', 'address 4', 'unit_town', 'unit_postCode', $country);

        static::assertEquals(
            [
                'addressLine1' => 'address 1',
                'addressLine2' => 'address 2',
                'addressLine3' => 'address 3',
                'addressLine4' => 'address 4',
                'town' => 'unit_town',
                'postcode' => 'unit_postCode',
                'countryCode' => 9999,
            ],
            $sut->toArray()
        );

        // check with country code is null
        $sut->updateAddress('address 1', 'address 2', 'address 3', 'address 4', 'unit_town', 'unit_postcode', null);

        static::assertNull($sut->toArray()['countryCode']);
    }

    public function testIsEmpty(): void
    {
        $address = new Address();

        $this->assertEquals(true, $address->isEmpty());

        $address->updateAddress('address 1');
        $this->assertEquals(false, $address->isEmpty());
    }
}
