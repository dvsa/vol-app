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
        $sectionDetails = ['status' => 1]; // VariationCompletionEntityService::STATUS_REQUIRES_ATTENTION
        $ref = 'people';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
            'licence' => [
                'organisation' => [
                    'type' => [
                        'id' => 'org_t_llp'
                    ]
                ]
            ]
        ];

        $viewModel = new VariationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $viewModel);
        $this->assertEquals('section.name.people.org_t_llp', $viewModel->getVariable('name'));
        $this->assertEquals('orange', $viewModel->getVariable('statusColour'));
        $this->assertEquals('REQUIRES ATTENTION', $viewModel->getVariable('status'));

        // variation sections should NOT be visibly numbered
        $this->assertNull($viewModel->getVariable('sectionNumber')); // OLCS-7016;

    }

    public function testViewWithUpdated()
    {
        $sectionDetails = ['status' => 2]; // VariationCompletionEntityService::STATUS_UPDATED
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
            'licence' => [
                'organisation' => [
                    'type' => [
                        'id' => 'org_t_llp'
                    ]
                ]
            ]
        ];

        $viewModel = new VariationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $viewModel);

        $this->assertEquals('green', $viewModel->getVariable('statusColour'));
        $this->assertEquals('UPDATED', $viewModel->getVariable('status'));

        $this->assertNull($viewModel->getVariable('sectionNumber'));
    }

    public function testViewWithUnchanged()
    {
        $sectionDetails = ['status' => 0]; // VariationCompletionEntityService::STATUS_UNCHANGED
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
            'licence' => [
                'organisation' => [
                    'type' => [
                        'id' => 'org_t_llp'
                    ]
                ]
            ]
        ];

        $viewModel = new VariationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $viewModel);

        $this->assertEquals('', $viewModel->getVariable('statusColour'));
        $this->assertEquals('', $viewModel->getVariable('status'));

        $this->assertNull($viewModel->getVariable('sectionNumber'));
    }

    public function testViewWithNotEnabled()
    {
        $sectionDetails = ['status' => 0, 'enabled' => 0];
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
            'licence' => [
                'organisation' => [
                    'type' => [
                        'id' => 'org_t_llp'
                    ]
                ]
            ]
        ];

        $viewModel = new VariationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $viewModel);

        $this->assertEquals('', $viewModel->getVariable('statusColour'));
        $this->assertEquals('', $viewModel->getVariable('status'));
        $this->assertEquals(0, $viewModel->getVariable('enabled'));

        $this->assertNull($viewModel->getVariable('sectionNumber'));
    }
}
