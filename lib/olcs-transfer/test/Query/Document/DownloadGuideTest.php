<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\Document\DownloadGuide;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\Document\DownloadGuide::class)]
final class DownloadGuideTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'identifier' => 'unit_id',
        ];

        $sut = DownloadGuide::create($data);

        $this->assertEquals('unit_id', $sut->getIdentifier());
    }
}
