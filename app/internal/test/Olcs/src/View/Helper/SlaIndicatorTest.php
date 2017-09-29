<?php


namespace OlcsTest\View\Helper;

use Olcs\View\Helper\SlaIndicator;

/**
 * Class SlaIndicatorTest
 *
 * @package OlcsTest\View\Helper
 */
class SlaIndicatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests the invoke method.
     */
    public function testInvoke()
    {
        $sut = new SlaIndicator();

        $this->assertInstanceOf('Olcs\View\Helper\SlaIndicator', $sut);
    }

    public function testHasTargetBeenMet()
    {
        $sut = $this->createPartialMock('Olcs\View\Helper\SlaIndicator', ['doHasTargetBeenMet']);

        $sut->expects($this->exactly(2))
            ->method('doHasTargetBeenMet')
            ->will($this->onConsecutiveCalls(false, true));

        $this->assertEquals(
            '<span class="status grey">Inactive</span>',
            $sut->hasTargetBeenMet()
        );
        $this->assertEquals(
            '<span class="status red">Fail</span>',
            $sut->hasTargetBeenMet('any', 'any')
        );
        $this->assertEquals(
            '<span class="status green">Pass</span>',
            $sut->hasTargetBeenMet('any', 'any')
        );
    }

    /**
     *
     * @dataProvider doHasTargetBeenMetProvider
     * @param string $dateFrom
     * @param string $targetDate
     * @param bool $boolean
     *
     * @return void
     */
    public function testDoHasTargetBeenMet($dateFrom, $targetDate, $boolean)
    {
        $sut = new SlaIndicator();

        $this->assertSame($boolean, $sut->doHasTargetBeenMet($dateFrom, $targetDate));
    }

    /**
     * Data provider.
     *
     * return array
     */
    public function doHasTargetBeenMetProvider()
    {
        return [
            [
                '2014-03-01',
                '2014-03-02',
                true
            ],
            [
                '2014-03-01',
                '2014-02-28',
                false
            ],
        ];
    }
}
