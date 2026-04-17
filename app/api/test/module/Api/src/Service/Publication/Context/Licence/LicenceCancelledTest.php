<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Licence;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Service\Publication\Context\Licence\LicenceCancelled;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class LicenceCancelledTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceCancelledTest extends MockeryTestCase
{
    /**
     *
     * @param $section
     * @param $expectedString
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the application licence cancelled filter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provideTestProvider')]
    public function testProvide(mixed $section, mixed $expectedString): void
    {

        $sut = new LicenceCancelled(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        $output = [
            'licenceCancelled' => $expectedString . $sut->createDate()
        ];

        $expectedOutput = new \ArrayObject($output);

        $publicationSection = new PublicationSectionEntity();
        $publicationSection->setId($section);

        $input = new PublicationLink();
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
        $sut = new LicenceCancelled(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        return [
            [PublicationSectionEntity::LIC_SURRENDERED_SECTION, $sut::LIC_SURRENDERED],
            [PublicationSectionEntity::LIC_TERMINATED_SECTION, $sut::LIC_TERMINATED],
            [PublicationSectionEntity::LIC_CNS_SECTION, $sut::LIC_CNS]
        ];
    }
}
