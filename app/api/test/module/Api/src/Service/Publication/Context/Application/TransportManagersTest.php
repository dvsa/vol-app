<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication as TransportManagerApplicationEntity;
use Dvsa\Olcs\Api\Service\Publication\Context\Application\TransportManagers;
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
        $publicationLink->shouldReceive('getApplication->getTransportManagers')
            ->andReturn(new ArrayCollection());

        $sut = new TransportManagers(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));

        $output = [];
        $expectedOutput = new \ArrayObject($output);

        $this->assertEquals($expectedOutput, $sut->provide($publicationLink, new \ArrayObject()));
    }

    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the application transport managers filter')]
    public function testProvide(): void
    {
        $tma1 = $this->setupTransportManagerApplication('Bill', 'Clinton', 'A');
        $tma2 = $this->setupTransportManagerApplication('Barbara', 'Bush', 'D');
        $tma3 = $this->setupTransportManagerApplication('Nancy', 'Reagan', 'U');

        $publicationLink = m::mock(PublicationLink::class);
        $publicationLink->shouldReceive('getApplication->getTransportManagers')
            ->andReturn(new ArrayCollection([$tma1, $tma2, $tma3]));

        $sut = new TransportManagers(m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class));
        $output = [
            'transportManagers' => 'Bill Clinton, Barbara Bush, Nancy Reagan',
            'applicationTransportManagers' => [$tma1->getTransportManager(), $tma3->getTransportManager()],
        ];

        $expectedOutput = new \ArrayObject($output);

        $this->assertEquals($expectedOutput, $sut->provide($publicationLink, new \ArrayObject()));
    }

    /**
     * Setup a TransportManagerApplication
     *
     * @param string $forename
     * @param string $familyName
     * @param string $action A, U, or D
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     */
    private function setupTransportManagerApplication(mixed $forename, mixed $familyName, mixed $action): mixed
    {
        $person = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person->setForename($forename);
        $person->setFamilyName($familyName);

        $cd = new \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails(new \Dvsa\Olcs\Api\Entity\System\RefData());
        $cd->setPerson($person);

        $tm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $tm->setHomeCd($cd);

        $tma = new TransportManagerApplicationEntity();
        $tma->setTransportManager($tm);
        $tma->setAction($action);

        return $tma;
    }
}
