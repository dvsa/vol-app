<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Licence;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\Licence\Text2 as LicenceText2;
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
        $sut = new LicenceText2();

        $person = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person->setForename('Bob')->setFamilyName('Smith');

        $input = [
            'licenceCancelled' => 'LICENCE_CANCELLED',
            'licencePeople' => [$person],
        ];

        $organisation = new Organisation();
        $organisation->setName('ORG');
        $organisation->setType(new RefData(Organisation::ORG_TYPE_LLP));
        $licence = new Licence($organisation, new RefData());

        $publicationLink = new PublicationLink();
        $publicationLink->setLicence($licence);

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $expectedString = "LICENCE_CANCELLED\nORG\nPartner(s): Bob Smith";
        $this->assertEquals($expectedString, $publicationLink->getText2());
    }

    public function testProcessNoCancelledText(): void
    {
        $sut = new LicenceText2();

        $input = [
            'licencePeople' => [],
        ];

        $organisation = new Organisation();
        $organisation->setName('ORG');
        $organisation->setType(new RefData(Organisation::ORG_TYPE_LLP));
        $licence = new Licence($organisation, new RefData());

        $publicationLink = new PublicationLink();
        $publicationLink->setLicence($licence);

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $expectedString = "ORG";
        $this->assertEquals($expectedString, $publicationLink->getText2());
    }
}
