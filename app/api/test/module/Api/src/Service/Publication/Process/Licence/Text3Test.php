<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Licence;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\Licence\Text3 as LicenceText3;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class Text3Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Text3Test extends MockeryTestCase
{
    public function testProcess(): void
    {
        $sut = new LicenceText3();

        $input = [
            'licenceAddress' => 'LICENCE_ADDRESS',
            'busNote' => 'BUS_NOTE',
        ];

        $publicationLink = new PublicationLink();

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $expectedString = "LICENCE_ADDRESS\nBUS_NOTE";
        $this->assertEquals($expectedString, $publicationLink->getText3());
    }

    public function testProcessMinData(): void
    {
        $sut = new LicenceText3();

        $input = [
            'licenceAddress' => 'LICENCE_ADDRESS',
        ];

        $publicationLink = new PublicationLink();

        $sut->process($publicationLink, new ImmutableArrayObject($input));

        $expectedString = "LICENCE_ADDRESS";
        $this->assertEquals($expectedString, $publicationLink->getText3());
    }
}
