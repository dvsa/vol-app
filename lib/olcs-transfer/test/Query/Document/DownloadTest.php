<?php

namespace Dvsa\OlcsTest\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\Document\Download;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\Document\Download
 */
class DownloadTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'identifier' => 123456,
        ];

        $sut = Download::create($data);

        static::assertEquals(123456, $sut->getIdentifier());
    }
}
