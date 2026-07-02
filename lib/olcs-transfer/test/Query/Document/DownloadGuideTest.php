<?php

namespace Dvsa\OlcsTest\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\Document\DownloadGuide;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Transfer\Query\Document\DownloadGuide
 */
class DownloadGuideTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'identifier' => 'unit_id',
        ];

        $sut = DownloadGuide::create($data);

        static::assertEquals('unit_id', $sut->getIdentifier());
    }
}
