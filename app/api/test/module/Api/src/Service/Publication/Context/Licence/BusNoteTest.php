<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Licence;

use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Service\Publication\Context\Licence\BusNote;
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
     * Test the application bus note filter
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideTestProvider')]
    public function testProvide(mixed $section, mixed $expectedString): void
    {
        $sut = new BusNote(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        $output = [
            'busNote' => sprintf($sut::BUS_STRING, $expectedString)
        ];
        $expectedOutput = new \ArrayObject($output);

        $publicationSection = new PublicationSectionEntity();
        $publicationSection->setId($section);

        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('isPsv')->andReturn(true);

        $input = new PublicationLink();
        $input->setLicence($licence);
        $input->setPublicationSection($publicationSection);

        $context = new \ArrayObject();
        $sut->provide($input, $context);

        $this->assertEquals($expectedOutput, $context);
    }

    public function testProvideNotPsv(): void
    {
        $sut = new BusNote(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        $output = [];
        $expectedOutput = new \ArrayObject($output);

        $publicationSection = new PublicationSectionEntity();
        $publicationSection->setId(PublicationSectionEntity::LIC_SURRENDERED_SECTION);

        $licence = m::mock(LicenceEntity::class);
        $licence->shouldReceive('isPsv')->andReturn(false);

        $input = new PublicationLink();
        $input->setLicence($licence);
        $input->setPublicationSection($publicationSection);

        $context = new \ArrayObject();
        $sut->provide($input, $context);

        $this->assertEquals($expectedOutput, $context);
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
            [PublicationSectionEntity::LIC_TERMINATED_SECTION, $sut::BUS_SURRENDERED],
            [PublicationSectionEntity::LIC_REVOKED_SECTION, $sut::BUS_REVOKED],
            [PublicationSectionEntity::LIC_CNS_SECTION, $sut::BUS_CNS]
        ];
    }
}
