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
    const INACTIVE_HTML = '<span class="status grey">Inactive</span>';
    const FAIL_HTML = '<span class="status red">Fail</span>';
    const PASS_HTML = '<span class="status green">Pass</span>';

    /**
     * Tests the invoke method.
     */
    public function testInvoke()
    {
        $sut = new SlaIndicator();

        $this->assertInstanceOf('Olcs\View\Helper\SlaIndicator', $sut);
        $this->assertSame($sut, $sut());
    }

    /**
     * @dataProvider provideGenerateItemCases
     *
     * @param      $queryResult
     * @param bool $html
     *
     * @return void
     */
    public function testGenerateItem($queryResult, $html)
    {
        $sut = new SlaIndicator();
        $this->assertSame(
            [
                'label' => 'DUMMY LABEL',
                'date' => $queryResult['date'],
                'suffix' => $html,
            ],
            $sut->generateDateItem('DUMMY LABEL', $queryResult, 'date')
        );
    }

    public function provideGenerateItemCases()
    {
        return [
            [
                [
                    'date' => '2014-03-01',
                    'dateTarget' => '2014-03-02'
                ],
                self::PASS_HTML,
            ],
            [
                [
                    'date' => '2014-03-01',
                    'dateTarget' => '2014-03-01',
                ],
                self::PASS_HTML,
            ],
            [
                [
                    'date' => '2014-03-02',
                    'dateTarget' => '2014-03-01',
                ],
                self::FAIL_HTML,
            ],
            [
                [
                    'date' => null,
                    'dateTarget' => '2014-03-01',
                ],
                self::INACTIVE_HTML,
            ],
            [
                [
                    'date' => '2014-03-01',
                    'dateTarget' => null,
                ],
                self::INACTIVE_HTML,
            ],
            [
                [
                    'date' => null,
                    'dateTarget' => null,
                ],
                self::INACTIVE_HTML,
            ],
            [
                [
                    'date' => null,
                ],
                self::INACTIVE_HTML,
            ],
            [
                [
                    'date' => '2014-03-01',
                ],
                self::INACTIVE_HTML,
            ],
        ];
    }

    /**
     * @dataProvider doHasTargetBeenMetProvider
     *
     * @param string $dateFrom
     * @param string $targetDate
     * @param bool   $boolean
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
