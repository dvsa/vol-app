<?php

/**
 * Internal Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Zend\Form\Form;
use Common\Controller\Lva;
use Common\View\Model\Section;
use Olcs\View\Model\Application\Layout;
use Olcs\View\Model\Application\CreateApplicationLayout;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Internal Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends Lva\AbstractTypeOfLicenceController
{
    use ApplicationControllerTrait,
        Lva\Traits\ApplicationTypeOfLicenceTrait;

    protected $location = 'internal';
    protected $lva = 'application';

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
