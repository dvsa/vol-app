<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Application;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\Application\Text2 as ApplicationText2;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class Text2Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Text2Test extends MockeryTestCase
{
    public function testProcess(): void
    {
        $sut = new ApplicationText2();

        $person = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person->setForename('Bob')->setFamilyName('Smith');

        $input = [
            'applicationPeople' => [$person],
        ];

        $organisation = new Organisation();
        $organisation->setName('ORG');
        $organisation->setType(new RefData(Organisation::ORG_TYPE_LLP));
        $licence = new Licence($organisation, new RefData());
        $application = new \Dvsa\Olcs\Api\Entity\Application\Application($licence, new RefData(), false);

        $publicationLink = new PublicationLink();
        $publicationLink->setApplication($application);

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $expectedString = "ORG\nPartner(s): Bob Smith";
        $this->assertEquals($expectedString, $publicationLink->getText2());
    }
}
