<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base;

use Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base\Stub\DynamicBookmarkStub;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark
 */
class DynamicBookmarkTest extends MockeryTestCase
{
    public function testValidateDataAndGetQuery()
    {
        /** @var DynamicBookmarkStub|m\MockInterface $sut */
        $sut = new DynamicBookmarkStub();

        $data = [
            'bar' => 1,
        ];

        $this->assertEquals('foo', $sut->validateDataAndGetQuery($data));
    }

    public function testValidateDataAndGetQueryThrowException()
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
