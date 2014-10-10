<?php

/**
 * Internal Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Zend\View\Model\ViewModel;
use Common\Controller\Traits\Lva;
use Olcs\View\Model\Application\Layout;
use Olcs\View\Model\Application\CreateApplicationLayout;

/**
 * Internal Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractApplicationController
{
    use Lva\TypeOfLicenceTrait,
        Lva\ApplicationTypeOfLicenceTrait;

    /**
     * Render the section
     *
     * @param ViewModel $content
     */
    protected function renderCreateApplication(ViewModel $content)
    {
        $applicationLayout = new CreateApplicationLayout();

        $applicationLayout->addChild($this->getQuickActions(), 'actions');
        $applicationLayout->addChild($content, 'content');

        $params = array(
            'applicationId' => '',
            'licNo' => '',
            'licenceId' => '',
            'companyName' => '',
            'status' => ''
        );

        return new Layout($applicationLayout, $params);
    }
}
