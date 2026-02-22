<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\PiHearing;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson as OrganisationPersonEntity;
use Dvsa\Olcs\Api\Entity\Organisation\TradingName as TradingNameEntity;
use Dvsa\Olcs\Api\Entity\Person\Person as PersonEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\PiHearing\HearingText1;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class HearingText1Test
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class HearingText1Test extends MockeryTestCase
{
    /**
     *
     * @param $organisationType
     * @param $personPrefix
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the hearing text1 filter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('processTestProvider')]
    public function testProcess(mixed $organisationType, mixed $personPrefix): void
    {

        $sut = new HearingText1();

        $pi = 99;
        $licenceAddress = 'line 1, line 2, line 3, line 4, town, postcode';
        $licNo = 'OB1234567';
        $licenceType = 'SN';
        $venueOther = 'Pi Venue Information';
        $hearingDate = '12 May 2014';
        $previousHearingDate = '3 April 2014';
        $hearingTime = '14:30';
        $organisationName = 'Organisation Name';
        $organisationTradingName = 'Organisation Trading Name';
        $organisationTradingName2 = 'Organisation Trading Name 2';
        $previousPublication = 6830;
        $personForename = 'John';
        $personFamilyName = 'Smith';

        $personName = $personForename . ' ' . $personFamilyName;

        $tradingName = m::mock(TradingNameEntity::class);
        $tradingName->shouldReceive('getName')->andReturn($organisationTradingName);
        $tradingName2 = m::mock(TradingNameEntity::class);
        $tradingName2->shouldReceive('getName')->andReturn($organisationTradingName2);
        $tradingNames = new ArrayCollection([$tradingName, $tradingName2]);

        $personMock = m::mock(PersonEntity::class);
        $personMock->shouldReceive('getForename')->andReturn($personForename);
        $personMock->shouldReceive('getFamilyName')->andReturn($personFamilyName);

        $organisationPerson = m::mock(OrganisationPersonEntity::class);
        $organisationPerson->shouldReceive('getPerson')->andReturn($personMock);
        $organisationPersons = new ArrayCollection([$organisationPerson]);

        $organisationMock = m::mock(OrganisationEntity::class);
        $organisationMock->shouldReceive('getName')->andReturn($organisationName);
        $organisationMock->shouldReceive('getTradingNames')->andReturn($tradingNames);
        $organisationMock->shouldReceive('getOrganisationPersons')->andReturn($organisationPersons);
        $organisationMock->shouldReceive('getType->getId')->andReturn($organisationType);

        $licenceMock = m::mock(LicenceEntity::class);
        $licenceMock->shouldReceive('getOrganisation')->andReturn($organisationMock);
        $licenceMock->shouldReceive('getLicNo')->andReturn($licNo);
        $licenceMock->shouldReceive('getLicenceTypeShortCode')->andReturn($licenceType);

        $publicationLink = m::mock(PublicationLink::class)->makePartial();
        $publicationLink->shouldReceive('getLicence')->andReturn($licenceMock);
        $publicationLink->shouldReceive('getLicence->getOrganisation')->andReturn($organisationMock);
        $publicationLink->shouldReceive('getPi->getId')->andReturn($pi);

        $input = [
            'previousPublication' => $previousPublication,
            'previousHearing' => $previousHearingDate,
            'licenceAddress' => $licenceAddress,
            'venueOther' => $venueOther,
            'formattedHearingDate' => $hearingDate,
            'formattedHearingTime' => $hearingTime,
        ];

        $expectedString = sprintf(
            'Public Inquiry (%s) to be held at %s, on %s commencing at %s (Previous Publication:'
            . '(%s)) Previous hearing on %s was adjourned. '
            . "\n" . '%s %s '
            . "\n" . '%s'
            . "\n" . 'T/A %s '
            . "\n" . '%s '
            . "\n" . '%s',
            $pi,
            $venueOther,
            $hearingDate,
            $hearingTime,
            $previousPublication,
            $previousHearingDate,
            $licNo,
            $licenceType,
            strtoupper($organisationName),
            strtoupper($organisationTradingName2),
            $personPrefix . strtoupper($personName),
            strtoupper($licenceAddress)
        );

        $output = $sut->process($publicationLink, new ImmutableArrayObject($input));
        $this->assertEquals($expectedString, $output->getText1());
    }

    /**
     * Filter provider
     *
     * @return array
     */
    public static function processTestProvider(): array
    {
        return [
            [OrganisationEntity::ORG_TYPE_REGISTERED_COMPANY, 'Director(s): '],
            [OrganisationEntity::ORG_TYPE_LLP, 'Partner(s): '],
            ['', '']
        ];
    }
}
