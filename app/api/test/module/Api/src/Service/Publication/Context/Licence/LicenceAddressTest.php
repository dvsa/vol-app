<?php

declare(strict_types=1);

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
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the licence address filter')]
    public function testProvide(): void
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

    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the licence address filter')]
    public function testProvideWithNoAddress(): void
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

    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the licence address filter')]
    public function testProvideWithNoLicence(): void
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
