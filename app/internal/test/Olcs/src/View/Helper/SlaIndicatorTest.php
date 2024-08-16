<?php

namespace OlcsTest\View\Helper;

use Olcs\View\Helper\SlaIndicator;

/**
 * Class SlaIndicatorTest
 *
 * @package OlcsTest\View\Helper
 */
class SlaIndicatorTest extends \PHPUnit\Framework\TestCase
{
    public const INACTIVE_HTML = '<span class="status grey">Inactive</span>';
    public const FAIL_HTML = '<span class="status red">Fail</span>';
    public const PASS_HTML = '<span class="status green">Pass</span>';

    /**
     * Tests the invoke method.
     */
    public function testInvoke()
    {
        $sut = new SlaIndicator();

        $this->assertInstanceOf(\Olcs\View\Helper\SlaIndicator::class, $sut);
        $this->assertSame($sut, $sut());
    }

    /**
     * @dataProvider provideHasTargetBeenMetCases
     *
     * @param $date
     * @param $target
     * @param $result
     *
     * @return void
     */
    public function testHasTargetBeenMet($date, $target, $result)
    {
        $sut = new SlaIndicator();
        $this->assertSame(
            $result,
            $sut->hasTargetBeenMet($date, $target)
        );
    }

    public function provideHasTargetBeenMetCases()
    {
        return [
            [
                '2014-03-01',
                '2014-03-02',
                self::PASS_HTML,
            ],
            [
                '2014-03-01',
                '2014-03-01',
                self::PASS_HTML,
            ],
            [
                '2014-03-02',
                '2014-03-01',
                self::FAIL_HTML,
            ],
            [
                null,
                '2014-03-01',
                self::INACTIVE_HTML,
            ],
            [
                '2014-03-01',
                null,
                self::INACTIVE_HTML,
            ],
            [
                null,
                null,
                self::INACTIVE_HTML,
            ],
        ];
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
        foreach ($this->provideHasTargetBeenMetCases() as [$date, $target, $result]) {
            yield [
                [
                    'date' => $date,
                    'dateTarget' => $target,
                ],
                $result
            ];
        }
        yield [
            [
                'date' => null,
            ],
            self::INACTIVE_HTML,
        ];
        yield [
            [
                'date' => '2014-03-01',
            ],
            self::INACTIVE_HTML,
        ];
    }
}
