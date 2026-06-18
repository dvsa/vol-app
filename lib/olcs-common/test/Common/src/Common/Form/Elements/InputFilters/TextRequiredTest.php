<?php

/**
 * Test TextRequired InputFilter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters;

/**
 * Test TextRequired InputFilter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class TextRequiredTest extends \PHPUnit\Framework\TestCase
{
    public $filter;
    /**
     * test setup
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->filter = new InputFilters\TextRequired("text-required");
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
        $this->assertEquals('text-required', $this->getSpecificationElement('name'));
    }

    /**
     * ensure fields are required by default
     */
    public function testTextIsRequired(): void
    {
        $this->assertTrue($this->getSpecificationElement('required'));
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
}
