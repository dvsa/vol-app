<?php

/**
 * Test fee waive note input filter
 *
 * @author Alex Peshkov <alex.peshkov@clocal.co.uk>
 */

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters\FeeWaiveNote;

/**
 * Test fee waive note input filter
 *
 * @author Alex Peshkov <alex.peshkov@clocal.co.uk>
 */
class FeeWaiveNoteTest extends \PHPUnit\Framework\TestCase
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
        $this->element = new FeeWaiveNote();
    }

    /**
     * Test validators
     * @group feeWaiveNote
     */
    public function testValidators(): void
    {
        $spec = $this->element->getInputSpecification();
        $this->assertEquals($spec['validators'][0]['name'], \Laminas\Validator\StringLength::class);
        $this->assertEquals($spec['validators'][1]['name'], \Laminas\Validator\NotEmpty::class);
    }
}
