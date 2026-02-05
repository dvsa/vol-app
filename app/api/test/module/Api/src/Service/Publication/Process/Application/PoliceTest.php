<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Application;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class PoliceTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PoliceTest extends MockeryTestCase
{
    public function testProcessApplicationPeople(): void
    {
        $sut = new \Dvsa\Olcs\Api\Service\Publication\Process\Application\Police();

        $person1 = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person2 = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person3 = new \Dvsa\Olcs\Api\Entity\Person\Person();

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence(
            new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(),
            new \Dvsa\Olcs\Api\Entity\System\RefData()
        );
        $application = new \Dvsa\Olcs\Api\Entity\Application\Application(
            $licence,
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            true
        );

        $publicationLink = new PublicationLink();
        $publicationLink->setApplication($application);
        $publicationLink->addPoliceDatas(
            new \Dvsa\Olcs\Api\Entity\Publication\PublicationPoliceData($publicationLink, $person1)
        );

        $input = [
            'applicationPeople' => [$person2, $person3],
        ];
        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $this->assertSame($publicationLink->getPoliceDatas()->count(), 2);
        $this->assertSame($publicationLink->getPoliceDatas()[0]->getPerson(), $person2);
        $this->assertSame($publicationLink->getPoliceDatas()[1]->getPerson(), $person3);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestProcessNewApplication')]
    public function testProcessNewApplication(mixed $expectTmAdded, mixed $sectionId): void
    {
        $sut = new \Dvsa\Olcs\Api\Service\Publication\Process\Application\Police();

        $tm1 = $this->setupTransportManager();

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence(
            new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(),
            new \Dvsa\Olcs\Api\Entity\System\RefData()
        );
        $application = new \Dvsa\Olcs\Api\Entity\Application\Application(
            $licence,
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            false
        );

        $publicationSection = new PublicationSection();
        $publicationSection->setId($sectionId);

        $publicationLink = new PublicationLink();
        $publicationLink->setApplication($application);
        $publicationLink->setPublicationSection($publicationSection);

        $input = [
            'applicationTransportManagers' => [$tm1],
        ];
        $sut->process($publicationLink, new ImmutableArrayObject($input));

        if ($expectTmAdded) {
            $this->assertSame($publicationLink->getPoliceDatas()->count(), 1);
        } else {
            $this->assertSame($publicationLink->getPoliceDatas()->count(), 0);
        }
    }

    public static function dataProviderTestProcessNewApplication(): array
    {
        return [
            [true, PublicationSection::APP_NEW_SECTION],
            [true, PublicationSection::APP_GRANTED_SECTION],
            [false, PublicationSection::APP_GRANT_NOT_TAKEN_SECTION],
            [false, PublicationSection::APP_REFUSED_SECTION],
            [false, PublicationSection::APP_WITHDRAWN_SECTION],
        ];
    }

    public function testProcessTransportManagers(): void
    {
        $sut = new \Dvsa\Olcs\Api\Service\Publication\Process\Application\Police();

        $person1 = new \Dvsa\Olcs\Api\Entity\Person\Person();

        $tm1 = $this->setupTransportManager();
        $tm2 = $this->setupTransportManager();
        $tm3 = $this->setupTransportManager();

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence(
            new \Dvsa\Olcs\Api\Entity\Organisation\Organisation(),
            new \Dvsa\Olcs\Api\Entity\System\RefData()
        );
        $application = new \Dvsa\Olcs\Api\Entity\Application\Application(
            $licence,
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            true
        );

        $publicationLink = new PublicationLink();
        $publicationLink->setApplication($application);
        $publicationLink->addPoliceDatas(
            new \Dvsa\Olcs\Api\Entity\Publication\PublicationPoliceData($publicationLink, $person1)
        );

        $input = [
            'applicationTransportManagers' => [$tm1, $tm2, $tm3],
        ];
        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $this->assertSame($publicationLink->getPoliceDatas()->count(), 3);
        $this->assertSame($publicationLink->getPoliceDatas()[0]->getPerson(), $tm1->getHomeCd()->getPerson());
        $this->assertSame($publicationLink->getPoliceDatas()[1]->getPerson(), $tm2->getHomeCd()->getPerson());
        $this->assertSame($publicationLink->getPoliceDatas()[2]->getPerson(), $tm3->getHomeCd()->getPerson());
    }

    /**
     * Setup a stug transport manager
     *
     * @return \Dvsa\Olcs\Api\Entity\Tm\TransportManager
     */
    private function setupTransportManager(): mixed
    {
        $person = new \Dvsa\Olcs\Api\Entity\Person\Person();

        $cd = new \Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails(new \Dvsa\Olcs\Api\Entity\System\RefData());
        $cd->setPerson($person);

        $tm = new \Dvsa\Olcs\Api\Entity\Tm\TransportManager();
        $tm->setHomeCd($cd);

        return $tm;
    }
}
