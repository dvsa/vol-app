<?php

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters;
use Laminas\Validator\StringLength;

/**
 * Test SelectEmpty InputFilter
 * @covers \Common\Form\Elements\InputFilters\SelectEmpty
 */
class SelectEmptyTest extends \PHPUnit\Framework\TestCase
{
    public $filter;
    /**
     * test setup
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->filter = new InputFilters\SelectEmpty("test");
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
     * ensure select option is not required by default
     */
    public function testValueNotRequired(): void
    {
        $this->assertFalse($this->getSpecificationElement('required'));
    }
}
