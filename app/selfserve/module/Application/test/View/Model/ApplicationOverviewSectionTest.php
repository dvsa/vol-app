<?php

namespace Dvsa\Olcs\Application\View\Model;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\View\Model\ViewModel;

class ApplicationOverviewSectionTest extends MockeryTestCase
{
    public function testViewWithRequiresAttention()
    {
        $sectionDetails = ['enabled' => true];
        $ref = 'people';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
            'applicationCompletion' => [
                'peopleStatus' => 1
            ],
            'licence' => [
                'organisation' => [
                    'type' => [
                        'id' => 'org_t_llp'
                    ]
                ]
            ]
        ];

        $viewModel = new \Dvsa\Olcs\Application\View\Model\ApplicationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('section.name.people.org_t_llp', $viewModel->getVariable('name'));
        $this->assertEquals('orange', $viewModel->getVariable('statusColour'));
        $this->assertEquals('INCOMPLETE', $viewModel->getVariable('status'));
        $this->assertTrue($viewModel->getVariable('enabled'));
        $this->assertEquals(1, $viewModel->getVariable('sectionNumber'));
    }

    public function testViewWithUpdated()
    {
        $sectionDetails = ['enabled' => true];
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
            'applicationCompletion' => [
                'typeOfLicenceStatus' => 2
            ],
            'licence' => [
                'organisation' => [
                    'type' => [
                        'id' => 'org_t_llp'
                    ]
                ]
            ]
        ];

        $viewModel = new \Dvsa\Olcs\Application\View\Model\ApplicationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);

        $this->assertEquals('green', $viewModel->getVariable('statusColour'));
        $this->assertEquals('COMPLETE', $viewModel->getVariable('status'));
        $this->assertTrue($viewModel->getVariable('enabled'));
        $this->assertEquals(1, $viewModel->getVariable('sectionNumber'));
    }

    public function testViewWithUnchanged()
    {
        $sectionDetails = ['enabled' => false];
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
            'applicationCompletion' => [
                'typeOfLicenceStatus' => 0
            ],
            'licence' => [
                'organisation' => [
                    'type' => [
                        'id' => 'org_t_llp'
                    ]
                ]
            ]
        ];

        $viewModel = new \Dvsa\Olcs\Application\View\Model\ApplicationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);

        $this->assertEquals('grey', $viewModel->getVariable('statusColour'));
        $this->assertEquals('NOT STARTED', $viewModel->getVariable('status'));
        $this->assertFalse($viewModel->getVariable('enabled'));
        $this->assertEquals(1, $viewModel->getVariable('sectionNumber'));
    }
}
