<?php

/**
 * Application Overview Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace CommonTest\BusinessRule\Rule;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\BusinessRule\Rule\ApplicationOverview as Sut;

/**
 * Application Overview Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationOverviewTest extends MockeryTestCase
{
    protected $sut;

    protected $brm;

    public function setUp()
    {
        $this->sut = new Sut();
    }

    public function testFilter()
    {
        $data = [
            'id' => 69,
            'version' => 2,
            'receivedDate' => '2015-04-08',
            'targetCompletionDate' => '2016-04-08',
            'leadTcArea' => 'N',
            'foo' => 'bar'
        ];

        $expected = [
            'id' => 69,
            'version' => 2,
            'receivedDate' => '2015-04-08',
            'targetCompletionDate' => '2016-04-08',
        ];

        $this->assertEquals($expected, $this->sut->filter($data));
    }

    public function testFilterWithoutTcd()
    {
        $data = [
            'id' => 69,
            'version' => 2,
            'receivedDate' => '2015-04-08',
            'leadTcArea' => 'N',
            'foo' => 'bar'
        ];

        $expected = [
            'id' => 69,
            'version' => 2,
            'receivedDate' => '2015-04-08'
        ];

        $this->assertEquals($expected, $this->sut->filter($data));
    }
}
