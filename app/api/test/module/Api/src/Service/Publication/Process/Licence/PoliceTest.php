<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Licence;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class PoliceTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class PoliceTest extends MockeryTestCase
{
    public function testProcessApplicationPeople(): void
    {
        $sut = new \Dvsa\Olcs\Api\Service\Publication\Process\Licence\Police();

        $person1 = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person2 = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person3 = new \Dvsa\Olcs\Api\Entity\Person\Person();

        $publicationLink = new PublicationLink();
        $publicationLink->addPoliceDatas(
            new \Dvsa\Olcs\Api\Entity\Publication\PublicationPoliceData($publicationLink, $person1)
        );

        $input = [
            'licencePeople' => [$person2, $person3],
        ];
        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $this->assertCount(2, $publicationLink->getPoliceDatas());
        $this->assertSame($publicationLink->getPoliceDatas()[0]->getPerson(), $person2);
        $this->assertSame($publicationLink->getPoliceDatas()[1]->getPerson(), $person3);
    }
}
