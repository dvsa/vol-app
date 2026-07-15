<?php

/**
 * Test VehicleSafetyTachographAnalyserContractor
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters\VehicleSafetyTachographAnalyserContractor;
use Common\Form\Elements\Validators\VehicleSafetyTachographAnalyserContractorValidator;

/**
 * Test VehicleSafetyTachographAnalyserContractor
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class VehicleSafetyTachographAnalyserContractorTest extends \PHPUnit\Framework\TestCase
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
        $this->element = new VehicleSafetyTachographAnalyserContractor();
    }

    /**
     * Test validators
     */
    public function testValidators(): void
    {
        $spec = $this->element->getInputSpecification();

        $this->assertInstanceOf(\Common\Form\Elements\Validators\VehicleSafetyTachographAnalyserContractorValidator::class, $spec['validators'][1]);
    }
}
