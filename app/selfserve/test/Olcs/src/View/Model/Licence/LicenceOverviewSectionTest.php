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
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'licence',
            'sectionNumber' => 1
        ];

        $viewModel = new LicenceOverviewSection($ref, $data);

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $viewModel);
        $this->assertEquals($ref, $viewModel->getVariable('anchorRef'));
    }
}
