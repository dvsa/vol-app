<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\Document\Download;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\Document\Download::class)]
final class DownloadTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'identifier' => 123456,
        ];

        $sut = Download::create($data);

        $this->assertEquals(123456, $sut->getIdentifier());
    }
}
