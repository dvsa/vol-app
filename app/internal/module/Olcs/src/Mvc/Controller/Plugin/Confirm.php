<?php
namespace Olcs\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\View\Model\ViewModel;

/**
 * Class Confirm - Generates validates and processes the confirm form
 *
 * @package Olcs\Mvc\Controller\Plugin
 */
class Confirm extends AbstractPlugin
{
    public function __invoke($label)
    {
        $form = $this->getController()->getForm('Confirm');

        if ($this->getController()->getRequest()->isPost()) {
            $form->setData($this->getController()->params()->fromPost());
            if ($form->isValid()) {
                return true;
            }
        }

        $view = new ViewModel();

        $view->setVariable('form', $form);
        $view->setVariable('label', $label);

        $view->setTemplate('partials/confirm');

        return $view;
    }
}
