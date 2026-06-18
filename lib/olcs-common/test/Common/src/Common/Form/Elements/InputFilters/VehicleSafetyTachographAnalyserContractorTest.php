<?php

/**
 * Test VehicleSafetyTachographAnalyserContractor
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters\VehicleSafetyTachographAnalyserContractor;
use Common\Form\Elements\Validators\VehicleSafetyTachographAnalyserContractorValidator;

/**
 * Test VehicleSafetyTachographAnalyserContractor
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehicleSafetyTachographAnalyserContractorTest extends \PHPUnit\Framework\TestCase
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

        $this->assertTrue($spec['validators'][1] instanceof VehicleSafetyTachographAnalyserContractorValidator);
    }
}
