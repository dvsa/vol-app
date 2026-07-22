<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\Document\AbstractDownload;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\Document\AbstractDownload::class)]
final class AbstractDownloadTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $data = [
            'isInline' => 'unit_Inline',
        ];

        $class = new class extends AbstractDownload {
        };

        $sut = $class::create($data);

        $this->assertEquals('unit_Inline', $sut->isInline());
    }
}
