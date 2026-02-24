<?php

declare(strict_types=1);

namespace PermitsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Permits\Data\Mapper\PermitTypeTitle;

/**
 * PermitTypeTitleTest
 */
class PermitTypeTitleTest extends TestCase
{
    private $pageTypeTitle;

    public function setUp(): void
    {
        $this->pageTypeTitle = new PermitTypeTitle();
    }

    public function testMapForDisplay(): void
    {
        $data = [
            'checkedAnswers' => 1,
            'id' => 1,
            'irhpPermitType' =>
                [
                    'id' => 4,
                    'name' => ['description' => 'Annual Bilateral']
                ]
        ];

        $expectedData = array_merge(
            $data,
            ['prependTitle' => 'Annual Bilateral']
        );

        $this->assertEquals(
            $expectedData,
            $this->pageTypeTitle->mapForDisplay($data)
        );
    }
}
