<?php

/**
 * Licence Overview Section Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\View\Model\Licence;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\View\Model\Licence\LicenceOverviewSection;

/**
 * Licence Overview Section Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceOverviewSectionTest extends MockeryTestCase
{
    public function testView()
    {
        $ref = 'people';
        $data = [
            'id' => 1,
            'idIndex' => 'licence',
            'sectionNumber' => 1,
            'organisation' => [
                'type' => [
                    'id' => 'org_t_llp'
                ]
            ]
        ];

        $viewModel = new LicenceOverviewSection($ref, $data);
        $this->assertEquals('section.name.people.org_t_llp', $viewModel->getVariable('name'));
        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $viewModel);
        $this->assertEquals($ref, $viewModel->getVariable('anchorRef'));
    }
}
