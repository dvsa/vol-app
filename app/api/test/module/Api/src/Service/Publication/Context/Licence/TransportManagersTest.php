<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Licence;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\Context\Licence\TransportManagers;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class TransportManagersTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TransportManagersTest extends MockeryTestCase
{
    public function testProvideEmpty(): void
    {
        $publicationLink = m::mock(PublicationLink::class);
        $publicationLink->shouldReceive('getLicence->getTmLicences')
            ->andReturn(new ArrayCollection());

        $sut = new TransportManagers(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        $context = new \ArrayObject();
        $sut->provide($publicationLink, $context);

        $this->assertEquals([], $context->getArrayCopy());
    }

    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the application transport managers filter')]
    public function testProvide(): void
    {
        $tma1 = $this->setupTransportManagerApplication();
        $tma2 = $this->setupTransportManagerApplication();

        $publicationLink = m::mock(PublicationLink::class);
        $publicationLink->shouldReceive('getLicence->getTmLicences')
            ->andReturn(new ArrayCollection([$tma1, $tma2]));

        $sut = new TransportManagers(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        $context = new \ArrayObject();
        $sut->provide($publicationLink, $context);

        $output = [
            'licenceTransportManagers' => [$tma1->getTransportManager(), $tma2->getTransportManager()],
        ];
        $expectedOutput = new \ArrayObject($output);
        $this->assertEquals($expectedOutput, $context);
    }

    /**
     * Setup a TransportManagerApplication
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     */
    private function setupTransportManagerApplication(): mixed
    {
        $tm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence(
            new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(),
            new \Dvsa\Olcs\Api\Entity\System\RefData()
        );

        $tml = new \Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence($licence, $tm);
        $tml->setTransportManager($tm);

        return $tml;
    }
}
