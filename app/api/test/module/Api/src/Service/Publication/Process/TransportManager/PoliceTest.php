<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\TransportManager;

use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Publication\PublicationPoliceData;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\TransportManager\Police;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class PoliceTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PoliceTest extends MockeryTestCase
{
    public function testProcess(): void
    {
        $sut = new Police();

        $person1 = m::mock(Person::class)->makePartial();
        $person2 = m::mock(Person::class)->makePartial();
        $person3 = m::mock(Person::class)->makePartial();

        $publicationLink = new PublicationLink();
        $publicationLink->addPoliceDatas(
            new PublicationPoliceData($publicationLink, $person1)
        );

        $input = [
            'tmPeople' => [$person2, $person3],
        ];
        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $this->assertSame($publicationLink->getPoliceDatas()->count(), 2);
        $this->assertSame($publicationLink->getPoliceDatas()[0]->getPerson(), $person2);
        $this->assertSame($publicationLink->getPoliceDatas()[1]->getPerson(), $person3);
    }
}
