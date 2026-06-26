<?php

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters;

/**
 * @covers \Common\Form\Elements\InputFilters\Text
 */
class TextTest extends \PHPUnit\Framework\TestCase
{
    /** @var  InputFilters\Text */
    private $filter;

    /**
     * test setup
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->filter = new InputFilters\Text("test");
    }

    /**
     * helper to extract a key out of the specification array
     *
     * @param string $key key to extract
     *
     * @return mixed
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
     * ensure text fields aren't required by default
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
    public function testSetMinMax(): void
    {
        $this->filter
            ->setMin(99)
            ->setMax(888);

        $validators = $this->getSpecificationElement('validators');
        $options = current($validators)['options'];

        static::assertEquals(99, $options['min']);
        static::assertEquals(888, $options['max']);
    }

    /**
     * Test set max
     */
    public function testIsAllowEmpty(): void
    {
        $this->filter
            ->setMin(0)
            ->setMax(0)
            ->setAllowEmpty(true);

        $validators = $this->getSpecificationElement('validators');

        static::assertEquals(
            [
                'name' => \Laminas\Validator\NotEmpty::class,
                'options' => [
                    'type' => \Laminas\Validator\NotEmpty::PHP,
                ],
            ],
            current($validators)
        );
    }
}
