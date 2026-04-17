<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base;

use Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base\Stub\DynamicBookmarkStub;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark::class)]
class DynamicBookmarkTest extends MockeryTestCase
{
    public function testValidateDataAndGetQuery(): void
    {
        /** @var DynamicBookmarkStub|m\MockInterface $sut */
        $sut = new DynamicBookmarkStub();

        $data = [
            'bar' => 1,
        ];

        $this->assertEquals('foo', $sut->validateDataAndGetQuery($data));
    }

    public function testValidateDataAndGetQueryThrowException(): void
    {
        $this->expectException(
            \Exception::class
        );

        /** @var DynamicBookmarkStub|m\MockInterface $sut */
        $sut = new DynamicBookmarkStub();

        $data = [
            'foo' => 1
        ];

        $sut->validateDataAndGetQuery($data);
    }
}
