<?php

/**
 * Test TableRequired
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters\TableRequired;
use Common\Form\Elements\Validators\TableRequiredValidator;

/**
 * Test TableRequired
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class TableRequiredTest extends \PHPUnit\Framework\TestCase
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

        $this->assertInstanceOf(\Common\Form\Elements\Validators\TableRequiredValidator::class, $spec['validators'][0]);
    }
}
