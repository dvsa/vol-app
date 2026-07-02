<?php

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters;
use Laminas\Validator\StringLength;

/**
 * Test Hidden InputFilter
 * @covers \Common\Form\Elements\InputFilters\Hidden
 */
class HiddenTest extends \PHPUnit\Framework\TestCase
{
    public $filter;
    /**
     * test setup
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->filter = new InputFilters\Hidden("test");
    }

    /**
     * helper to extract a key out of the specification array
     *
     * @param string $key key to extract
     *
     * @return array
     */
    protected function getSpecificationElement($key)
    {
        return $this->filter->getInputSpecification()[$key];
    }

    /**
     * test basic name
     */
    public function testGetInputSpecificationReturnsCorrectName(): void
    {
        $this->assertEquals('test', $this->getSpecificationElement('name'));
    }

    /**
     * ensure hidden fields aren't required by default
     */
    public function testTextNotRequired(): void
    {
        $this->assertFalse($this->getSpecificationElement('required'));
    }

    /**
     * ensure we trim all input strings
     */
    public function testStringTrimFilterIsUsed(): void
    {
        $this->assertEquals(
            [['name' => \Laminas\Filter\StringTrim::class]],
            $this->getSpecificationElement('filters')
        );
    }

    /**
     * Test set max
     */
    public function testSetMax(): void
    {
        $this->filter->setMax(10);

        $this->assertEquals(10, $this->getSpecificationElement('validators')[1]['options']['max']);
    }
}
