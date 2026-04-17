<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Process\Impounding;

use Common\Service\Data\RefData;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Api\Service\Publication\ImmutableArrayObject;
use Dvsa\Olcs\Api\Service\Publication\Process\Impounding\Text2;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class Text2Test
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class Text2Test extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Group('publicationFilter
Test the hearing text1 filter')]
    public function testProcess(): void
    {

        $sut = new Text2();

        $impoundingId = 99;

        $vrm = 'AB12CDE';

        $legislation1 = m::mock(RefDataEntity::class);
        $legislation2 = m::mock(RefDataEntity::class);
        $legislation1->shouldReceive('getDescription')->andReturn('leg1');
        $legislation2->shouldReceive('getDescription')->andReturn('leg2');
        $legislationTypes = new ArrayCollection([$legislation1, $legislation2]);

        $publicationLink = m::mock(PublicationLink::class)->makePartial();
        $publicationLink->shouldReceive('getImpounding->getId')->andReturn($impoundingId);
        $publicationLink->shouldReceive('getImpounding->getVrm')->andReturn($vrm);
        $publicationLink->shouldReceive('getImpounding->getImpoundingLegislationTypes')->andReturn($legislationTypes);

        $input = [];

        $expectedString = sprintf(
            'The applicants listed above have applied for the return of %s under the following; %s',
            $vrm,
            'leg1; leg2'
        );

        $output = $sut->process($publicationLink, new ImmutableArrayObject($input));
        $this->assertEquals($expectedString, $output->getText2());
    }
}
