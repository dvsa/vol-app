<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\Surrender\Section;

use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\Surrender\Section\LicenceDetailsService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\I18n\Translator\TranslatorInterface;

class LicenceDetailsServiceTest extends MockeryTestCase
{
    /** @var LicenceDetailsService review service */
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new LicenceDetailsService($abstractReviewServiceServices);
    }

    public function testGetConfigFromData(): void
    {
        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getName')->andReturn('OrgName');

        $address = m::mock(Address::class);
        $address->shouldReceive('getAddressLine1')->andReturn('Address Line 1');
        $address->shouldReceive('getAddressLine2')->andReturn('Address Line 2');
        $address->shouldReceive('getAddressLine3')->andReturn('Address Line 3');
        $address->shouldReceive('getAddressLine4')->andReturn('Address Line 4');
        $address->shouldReceive('getTown')->andReturn('Town');
        $address->shouldReceive('getPostcode')->andReturn('Postcode');
        $address->shouldReceive('getCountryCode')->andReturn(null);

        $contactDetails = m::mock(ContactDetails::class);
        $contactDetails->shouldReceive('getAddress')->andReturn($address);

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getLicNo')->andReturn('ABC12345');
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getTradingName')->andReturn('Trading Name');
        $licence->shouldReceive('getCorrespondenceCd')->andReturn($contactDetails);

        $surrender = m::mock(Surrender::class);
        $surrender->shouldReceive('getLicence')->andReturn($licence);

        $markup = $this->sut->getConfigFromData($surrender);

        $expected = [
            'multiItems' => [
                [
                    [
                        'label' => 'surrender-review-licence-number',
                        'value' => 'ABC12345'
                    ],
                    [
                        'label' => 'surrender-review-licence-holder',
                        'value' => 'OrgName'
                    ],
                    [
                        'label' => 'surrender-review-licence-trading-name',
                        'value' => 'Trading Name'
                    ],
                    [
                        'label' => 'surrender-review-licence-correspondence-address',
                        'value' => 'Address Line 1, Address Line 2, Address Line 3, Address Line 4, Town, Postcode'
                    ],
                ]
            ]
        ];

        $this->assertEquals($expected, $markup);
    }
}
