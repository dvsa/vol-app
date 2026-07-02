<?php

/**
 * Test Checkbox
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\OlcsCheckbox;
use Laminas\Validator as LaminasValidator;

/**
 * Test OlcsCheckbox Element
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class OlcsCheckboxTest extends \PHPUnit\Framework\TestCase
{
    /**+
     * Holds the element
     */
    private $element;

    /**
     * Setup the element
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->element = new OlcsCheckbox();
    }

    /**
     * Test validators
     */
    public function testGetInputSpecification(): void
    {
        $this->element->getLabelOption('label_position');
        $this->element->getLabelOption('always_wrap');

        $this->assertEquals(
            \Laminas\Form\View\Helper\FormRow::LABEL_APPEND,
            $this->element->getLabelOption('label_position')
        );
        $this->assertTrue($this->element->getLabelOption('always_wrap'));
    }
}
