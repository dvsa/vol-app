<?php

/**
 * Test TableRequired
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters\TableRequired;
use Common\Form\Elements\Validators\TableRequiredValidator;

/**
 * Test TableRequired
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TableRequiredTest extends \PHPUnit\Framework\TestCase
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
        $this->element = new TableRequired();
    }

    /**
     * Test validators
     */
    public function testValidators(): void
    {
        $spec = $this->element->getInputSpecification();

        $this->assertTrue($spec['validators'][0] instanceof TableRequiredValidator);
    }
}
