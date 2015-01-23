<?php

/**
 * Variation Overview Section Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\View\Model\Variation;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\View\Model\Variation\VariationOverviewSection;

/**
 * Variation Overview Section Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOverviewSectionTest extends MockeryTestCase
{
    public function testViewWithRequiresAttention()
    {
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
            'applicationCompletions' => [
                [
                    'typeOfLicenceStatus' => 1
                ]
            ]
        ];

        $viewModel = new VariationOverviewSection($ref, $data);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $viewModel);

        $this->assertEquals('orange', $viewModel->getVariable('statusColour'));
        $this->assertEquals('REQUIRES ATTENTION', $viewModel->getVariable('status'));
    }

    public function testViewWithUpdated()
    {
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
            'applicationCompletions' => [
                [
                    'typeOfLicenceStatus' => 2
                ]
            ]
        ];

        $viewModel = new VariationOverviewSection($ref, $data);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $viewModel);

        $this->assertEquals('green', $viewModel->getVariable('statusColour'));
        $this->assertEquals('UPDATED', $viewModel->getVariable('status'));
    }

    public function testViewWithUnchanged()
    {
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
            'applicationCompletions' => [
                [
                    'typeOfLicenceStatus' => 0
                ]
            ]
        ];

        $viewModel = new VariationOverviewSection($ref, $data);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $viewModel);

        $this->assertEquals('', $viewModel->getVariable('statusColour'));
        $this->assertEquals('', $viewModel->getVariable('status'));
    }
}
