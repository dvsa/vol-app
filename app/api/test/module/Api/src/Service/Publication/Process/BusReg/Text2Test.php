<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\BusReg;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Organisation\TradingName as TradingNameEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\BusReg\Text2 as BusRegText2;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class Text2Test
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Text2Test extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the bus reg text 2 filter')]
    public function testProcess(): void
    {
        $sut = new BusRegText2();

        $organisationName = 'organisation name';
        $organisationTradingName1 = 'trading name 1';
        $organisationTradingName2 = 'trading name 2';
        $licenceAddress = 'licence address';

        $input = [
            'licenceAddress' => $licenceAddress
        ];

        $tradingName = m::mock(TradingNameEntity::class);
        $tradingName->shouldReceive('getName')->andReturn($organisationTradingName1);
        $tradingName2 = m::mock(TradingNameEntity::class);
        $tradingName2->shouldReceive('getName')->andReturn($organisationTradingName2);
        $tradingNames = new ArrayCollection([$tradingName, $tradingName2]);

        $organisationMock = m::mock(OrganisationEntity::class);
        $organisationMock->shouldReceive('getName')->andReturn($organisationName);
        $organisationMock->shouldReceive('getTradingNames')->andReturn($tradingNames);

        $licenceMock = m::mock(LicenceEntity::class);
        $licenceMock->shouldReceive('getOrganisation')->andReturn($organisationMock);

        $publicationLink = m::mock(PublicationLink::class)->makePartial();
        $publicationLink->shouldReceive('getLicence->getOrganisation')->andReturn($organisationMock);

        $expectedString = strtoupper($organisationName . ' T/A ' . $organisationTradingName2 . ', ' . $licenceAddress);

        $output = $sut->process($publicationLink, new ImmutableArrayObject($input));
        $this->assertEquals($expectedString, $output->getText2());
    }
}
