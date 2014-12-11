<?php

/**
 * Operator People Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Operator;

/**
 * Operator People Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorPeopleController extends OperatorController
{
    /**
     * @var string
     */
    protected $section = 'people';

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $view = $this->getViewWithOrganisation();
        $view->setTemplate('view-new/pages/placeholder');
        return $this->renderView($view);
    }
}
