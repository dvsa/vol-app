<?php

/**
 * Month Select Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\MonthSelect;

/**
 * Month Select Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class MonthSelectTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new MonthSelect('foo');
    }

    public function testGetInputSpecification(): void
    {
        $spec = $this->sut->getInputSpecification();

        $this->assertEquals('foo', $spec['name']);
        $this->assertEquals(null, $spec['required']);
        $this->assertCount(1, $spec['validators']);
        $this->assertCount(1, $spec['filters']);
        $this->assertInstanceOf(\Laminas\Validator\Regex::class, $spec['validators'][0]);

        // Test the filter
        $this->assertNull($spec['filters'][0]['options']['callback']('foo'));
        $this->assertNull($spec['filters'][0]['options']['callback'](['year' => '2015']));
        $this->assertEquals('2015-02', $spec['filters'][0]['options']['callback'](['year' => '2015', 'month' => '02']));
    }
}
