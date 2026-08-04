<?php

/**
 * Test fee waive note input filter
 *
 * @author Alex Peshkov <alex.peshkov@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters\FeeWaiveNote;

/**
 * Test fee waive note input filter
 *
 * @author Alex Peshkov <alex.peshkov@clocal.co.uk>
 */
final class FeeWaiveNoteTest extends \PHPUnit\Framework\TestCase
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
     */
    #[\PHPUnit\Framework\Attributes\Group('feeWaiveNote')]
    public function testValidators(): void
    {
        $spec = $this->element->getInputSpecification();
        $this->assertEquals(\Laminas\Validator\StringLength::class, $spec['validators'][0]['name']);
        $this->assertEquals(\Laminas\Validator\NotEmpty::class, $spec['validators'][1]['name']);
    }
}
