<?php

namespace PermitsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Permits\Data\Mapper\PermitTypeTitle;

/**
 * PermitTypeTitleTest
 */
class PermitTypeTitleTest extends TestCase
{
    private $pageTypeTitle;

    public function setUp()
    {
        $this->pageTypeTitle = new PermitTypeTitle();
    }

    public function testMapForDisplay()
    {
        $data = [
            'checkedAnswers' => 1,
            'id' => 1,
            'irhpPermitType' =>
                [
                    'id' => 4,
                    'name' => ['description' => 'Annual Bilateral (EU and EEA)']
                ]
        ];

        $expectedData = array_merge(
            $data,
            ['prependTitle' => 'Annual Bilateral (EU and EEA)']
        );

        $this->assertEquals(
            $expectedData,
            $this->pageTypeTitle->mapForDisplay($data)
        );
    }
}
