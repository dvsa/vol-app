<?php

declare(strict_types=1);

namespace OlcsTest\View\Helper;

use Olcs\View\Helper\SlaIndicator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SlaIndicatorTest extends TestCase
{
    public const INACTIVE_HTML = '<span class="status grey">Inactive</span>';
    public const FAIL_HTML = '<span class="status red">Fail</span>';
    public const PASS_HTML = '<span class="status green">Pass</span>';

    /**
     * Tests the invoke method.
     */
    public function testInvoke(): void
    {
        $sut = new SlaIndicator();

        $this->assertInstanceOf(SlaIndicator::class, $sut);
        $this->assertSame($sut, $sut());
    }

    /**
     *
     * @param $date
     * @param $target
     * @param $result
     * @return void
     */
    #[DataProvider('provideHasTargetBeenMetCases')]
    public function testHasTargetBeenMet(mixed $date, mixed $target, mixed $result): void
    {
        $sut = new SlaIndicator();
        $this->assertSame(
            $result,
            $sut->hasTargetBeenMet($date, $target)
        );
    }

    public static function provideHasTargetBeenMetCases(): array
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
     *
     * @param      $queryResult
     * @param bool $html
     * @return void
     */
    #[DataProvider('provideGenerateItemCases')]
    public function testGenerateItem(mixed $queryResult, mixed $html): void
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

    public static function provideGenerateItemCases(): \Generator
    {
        foreach (self::provideHasTargetBeenMetCases() as [$date, $target, $result]) {
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
