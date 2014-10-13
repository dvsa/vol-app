<?php

/**
 * Internal Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Zend\Form\Form;
use Common\View\Model\Section;
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
     * @param string $titleSuffix
     * @param \Zend\Form\Form $form
     * @return \Common\View\Model\Section
     */
    protected function renderCreateApplication($titleSuffix, Form $form = null)
    {
        $content = new Section(array('title' => 'lva.section.title.' . $titleSuffix, 'form' => $form));

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
