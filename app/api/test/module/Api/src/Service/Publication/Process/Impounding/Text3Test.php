<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Impounding;

use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\Impounding\Text3;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class Text3Test
 * @author Teja Vaddala <teja.vaddala@dvsa.gov.uk>
 */
class Text3Test extends MockeryTestCase
{
    public function testProcess(): void
    {

        $sut = new Text3();

        $publicationLink = m::mock(PublicationLink::class)->makePartial();

        $input = [
            'outcome' => 'Withdrawn',
        ];

        $output = $sut->process($publicationLink, new ImmutableArrayObject($input));
        $this->assertEquals('Withdrawn', $output->getText3());
    }
}
