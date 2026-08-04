<?php

/**
 * Year Select Test
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\YearSelect;

/**
 * Year Select Test
 */
final class YearSelectTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new YearSelect('foo');
    }

    public function testSetOptionsMinAndMaxYear(): void
    {
        $options = [
            'max_year_delta' => '+5',
            'min_year_delta' => '-5'
        ];

        $year = date('Y');

        $this->sut->setOptions($options);

        $this->assertEquals(($year + 5), $this->sut->getMaxYear());
        $this->assertEquals(($year - 5), $this->sut->getMinYear());
        $this->assertCount(11, $this->sut->getValueOptions());
    }

    public function testSetOptionsMaxYear(): void
    {
        $options = [
            'max_year_delta' => '+5'
        ];

        $year = date('Y');

        $this->sut->setOptions($options);

        $this->assertEquals(($year + 5), $this->sut->getMaxYear());
        $this->assertEquals($year, $this->sut->getMinYear());
        $this->assertCount(6, $this->sut->getValueOptions());
    }

    public function testSetOptionsMinYear(): void
    {
        $options = [
            'min_year_delta' => '-5'
        ];

        $year = date('Y');

        $this->sut->setOptions($options);

        $this->assertEquals($year, $this->sut->getMaxYear());
        $this->assertEquals(($year - 5), $this->sut->getMinYear());
        $this->assertCount(6, $this->sut->getValueOptions());
    }
}
