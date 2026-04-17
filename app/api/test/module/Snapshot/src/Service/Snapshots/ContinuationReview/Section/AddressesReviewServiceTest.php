<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ContinuationReview\Section;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ContinuationReview\Section\AddressesReviewService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Business details review service test
 */
class AddressesReviewServiceTest extends MockeryTestCase
{
    /** @var AddressesReviewService review service */
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new AddressesReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromData(): void
    {
        $continuationDetail = new ContinuationDetail();

        /** @var Address $correspondenceAddress */
        $correspondenceAddress = new Address();
        $correspondenceAddress->setAddressLine1('Flat 1');
        $correspondenceAddress->setAddressLine2('Foo house');
        $correspondenceAddress->setPostcode('LS9 6NF');
        $correspondenceAddress->setTown('Leeds');

        /** @var Address $establishmentAddress */
        $establishmentAddress = new Address();
        $establishmentAddress->setAddressLine1('Flat 99');
        $establishmentAddress->setAddressLine2('Bar house');
        $establishmentAddress->setPostcode('SW1A 2AA');
        $establishmentAddress->setTown('London');

        $mockCorrespondenceCd = m::mock(ContactDetails::class)
            ->shouldReceive('getAddress')
            ->andReturn($correspondenceAddress)
            ->once()
            ->shouldReceive('getPhoneContactNumber')
            ->andReturn('123')
            ->with(RefData::PHONE_NUMBER_PRIMARY_TYPE)
            ->once()
            ->shouldReceive('getPhoneContactNumber')
            ->with(RefData::PHONE_NUMBER_SECONDARY_TYPE)
            ->andReturn('456')
            ->once()
            ->getMock();

        $mockEstablishmentCd = m::mock(ContactDetails::class)
            ->shouldReceive('getAddress')
            ->andReturn($establishmentAddress)
            ->once()
            ->getMock();

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($mockCorrespondenceCd)
            ->once()
            ->shouldReceive('getEstablishmentCd')
            ->andReturn($mockEstablishmentCd)
            ->once()
            ->shouldReceive('getLicenceType')
            ->andReturn(
                m::mock()
                ->shouldReceive('getId')
                ->andReturn(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL)
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $continuationDetail->setLicence($mockLicence);

        $expected = [
            [
                ['value' => 'continuation-review-addresses-correspondence-address'],
                ['value' => 'Flat 1, Foo house, Leeds, LS9 6NF', 'header' => true]
            ],
            [
                ['value' => 'continuation-review-addresses-establishment-address'],
                ['value' => 'Flat 99, Bar house, London, SW1A 2AA', 'header' => true]
            ],
            [
                ['value' => 'continuation-review-addresses-primary-number'],
                ['value' => '123', 'header' => true]
            ],
            [
                ['value' => 'continuation-review-addresses-secondary-number'],
                ['value' => '456', 'header' => true]
            ],
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($continuationDetail));
    }

    public function testGetConfigFromDataNoEstablishmentAddress(): void
    {
        $continuationDetail = new ContinuationDetail();

        /** @var Address $correspondenceAddress */
        $correspondenceAddress = new Address();
        $correspondenceAddress->setAddressLine1('Flat 1');
        $correspondenceAddress->setAddressLine2('Foo house');
        $correspondenceAddress->setPostcode('LS9 6NF');
        $correspondenceAddress->setTown('Leeds');

        $mockCorrespondenceCd = m::mock(ContactDetails::class)
            ->shouldReceive('getAddress')
            ->andReturn($correspondenceAddress)
            ->once()
            ->shouldReceive('getPhoneContactNumber')
            ->andReturn(null)
            ->with(RefData::PHONE_NUMBER_PRIMARY_TYPE)
            ->once()
            ->shouldReceive('getPhoneContactNumber')
            ->with(RefData::PHONE_NUMBER_SECONDARY_TYPE)
            ->andReturn(null)
            ->once()
            ->getMock();

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($mockCorrespondenceCd)
            ->once()
            ->shouldReceive('getEstablishmentCd')
            ->andReturn(null)
            ->once()
            ->shouldReceive('getLicenceType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $continuationDetail->setLicence($mockLicence);

        $expected = [
            [
                ['value' => 'continuation-review-addresses-correspondence-address'],
                ['value' => 'Flat 1, Foo house, Leeds, LS9 6NF', 'header' => true]
            ],
            [
                ['value' => 'continuation-review-addresses-establishment-address'],
                ['value' => 'continuation-review-addresses-establishment-address-same', 'header' => true]
            ],
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($continuationDetail));
    }

    public function testGetConfigFromDataSpecialRestricted(): void
    {
        $continuationDetail = new ContinuationDetail();

        /** @var Address $correspondenceAddress */
        $correspondenceAddress = new Address();
        $correspondenceAddress->setAddressLine1('Flat 1');
        $correspondenceAddress->setAddressLine2('Foo house');
        $correspondenceAddress->setPostcode('LS9 6NF');
        $correspondenceAddress->setTown('Leeds');

        $mockCorrespondenceCd = m::mock(ContactDetails::class)
            ->shouldReceive('getAddress')
            ->andReturn($correspondenceAddress)
            ->once()
            ->shouldReceive('getPhoneContactNumber')
            ->andReturn(null)
            ->with(RefData::PHONE_NUMBER_PRIMARY_TYPE)
            ->once()
            ->shouldReceive('getPhoneContactNumber')
            ->with(RefData::PHONE_NUMBER_SECONDARY_TYPE)
            ->andReturn(null)
            ->once()
            ->getMock();

        $mockLicence = m::mock(Licence::class)
            ->shouldReceive('getCorrespondenceCd')
            ->andReturn($mockCorrespondenceCd)
            ->once()
            ->shouldReceive('getEstablishmentCd')
            ->andReturn(null)
            ->once()
            ->shouldReceive('getLicenceType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(Licence::LICENCE_TYPE_SPECIAL_RESTRICTED)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $continuationDetail->setLicence($mockLicence);

        $expected = [
            [
                ['value' => 'continuation-review-addresses-correspondence-address'],
                ['value' => 'Flat 1, Foo house, Leeds, LS9 6NF', 'header' => true]
            ]
        ];

        $this->assertEquals($expected, $this->sut->getConfigFromData($continuationDetail));
    }
}
