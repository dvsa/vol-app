<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Application;

use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Api\Service\Publication\Context\Application\BusNote;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class BusNoteTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusNoteTest extends MockeryTestCase
{
    /**
     *
     * @param $section
     * @param $expectedString
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the application bus note filter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideTestProvider')]
    public function testProvide(mixed $section, mixed $expectedString): void
    {
        $sut = new BusNote(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        $output = [
            'busNote' => sprintf($sut::BUS_STRING, $expectedString)
        ];

        $expectedOutput = new \ArrayObject($output);

        $status = m::mock(RefDataEntity::class);
        $status->shouldReceive('getId')->andReturn(LicenceEntity::LICENCE_CATEGORY_PSV);

        $publicationSection = new PublicationSectionEntity();
        $publicationSection->setId($section);

        $application = m::mock(ApplicationEntity::class);
        $application->shouldReceive('getGoodsOrPsv')->andReturn($status);

        $input = new PublicationLink();
        $input->setApplication($application);
        $input->setPublicationSection($publicationSection);

        $this->assertEquals($expectedOutput, $sut->provide($input, new \ArrayObject()));
    }

    /**
     * Filter provider
     *
     * @return array
     */
    public static function provideTestProvider(): array
    {
        $sut = new BusNote(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        return [
            [PublicationSectionEntity::LIC_SURRENDERED_SECTION, $sut::BUS_SURRENDERED],
            [PublicationSectionEntity::LIC_REVOKED_SECTION, $sut::BUS_REVOKED],
            [PublicationSectionEntity::LIC_CNS_SECTION, $sut::BUS_CNS]
        ];
    }
}
