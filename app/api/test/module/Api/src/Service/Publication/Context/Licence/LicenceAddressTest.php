<?php

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Licence;

use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Helper\FormatAddress;
use Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceAddress;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class LicenceAddressTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceAddressTest extends MockeryTestCase
{
    /**
     * @group publicationFilter
     *
     * Test the licence address filter
     */
    public function testProvide()
    {
        $licenceAddress = 'licence address';

        $addressEntityMock = m::mock(AddressEntity::class);
        $contactDetailsEntityMock = m::mock(ContactDetailsEntity::class);
        $contactDetailsEntityMock->shouldReceive('getAddress')->once()->andReturn($addressEntityMock);

        $licenceEntityMock = m::mock(LicenceEntity::class);
        $licenceEntityMock->shouldReceive('getCorrespondenceCd')->once()->andReturn($contactDetailsEntityMock);

        $publicationLink = m::mock(PublicationLink::class);
        $publicationLink->shouldReceive('getLicence')->once()->andReturn($licenceEntityMock);

        $mockAddressFormatter = m::mock(FormatAddress::class);
        $mockAddressFormatter->shouldReceive('format')->once()->andReturn($licenceAddress);

        $sut = new LicenceAddress(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));
        $sut->setAddressFormatter($mockAddressFormatter);

        $output = [
            'licenceAddress' => $licenceAddress
        ];

        $expectedOutput = new \ArrayObject($output);

        $this->assertEquals($expectedOutput, $sut->provide($publicationLink, new \ArrayObject()));
    }

    /**
     * @group publicationFilter
     *
     * Test the licence address filter
     */
    public function testProvideWithNoAddress()
    {
        $licenceEntityMock = m::mock(LicenceEntity::class);
        $licenceEntityMock->shouldReceive('getCorrespondenceCd')->once()->andReturn(null);

        $publicationLink = m::mock(PublicationLink::class);
        $publicationLink->shouldReceive('getLicence')->andReturn($licenceEntityMock);

        $sut = new LicenceAddress(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        $output = [
            'licenceAddress' => ''
        ];

        $expectedOutput = new \ArrayObject($output);

        $this->assertEquals($expectedOutput, $sut->provide($publicationLink, new \ArrayObject()));
    }

    /**
     * @group publicationFilter
     *
     * Test the licence address filter
     */
    public function testProvideWithNoLicence()
    {
        $publicationLink = m::mock(PublicationLink::class);
        $publicationLink->shouldReceive('getLicence')->andReturn(null);

        $sut = new LicenceAddress(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        $output = [
            'licenceAddress' => ''
        ];

        $expectedOutput = new \ArrayObject($output);

        $this->assertEquals($expectedOutput, $sut->provide($publicationLink, new \ArrayObject()));
    }
}
