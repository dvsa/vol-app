<?php

/**
 * Operator Licences & Applications Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Operator;

/**
 * Operator Licences & Applications Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorLicencesApplicationsController extends OperatorController
{
    /**
     * @var string
     */
    protected $section = 'licences_applications';

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $view = $this->getViewWithOrganisation();
        $view->setTemplate('pages/placeholder');
        return $this->renderView($view);
    }
}
