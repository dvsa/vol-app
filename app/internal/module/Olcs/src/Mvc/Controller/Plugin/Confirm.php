<?php

namespace Olcs\Mvc\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Laminas\View\Model\ViewModel;

/**
 * Class Confirm - Generates validates and processes the confirm form
 *
 * @package Olcs\Mvc\Controller\Plugin
 */
class Confirm extends AbstractPlugin
{
    public function __invoke($label, $setTerminal = false, $custom = '', $confirmBtnLabel = 'Continue', $cancelBtnLabel = 'Cancel')
    {
        $form = $this->getController()->getForm('Confirm');

        // we need it for multiple delete in non-modal environment
        $query = $this->getController()->params()->fromQuery();
        if ($query) {
            $separator = (parse_url($form->getAttribute('action'), PHP_URL_QUERY) == null) ? '?' : '&';
            $form->setAttribute(
                'action',
                $form->getAttribute('action') . $separator . http_build_query($query)
            );
        }

        $post = $this->getController()->params()->fromPost();
        if ($this->getController()->getRequest()->isPost() && isset($post['form-actions']['confirm'])) {
            $form->setData($post);
            if ($form->isValid()) {
                return true;
            }
        }

        if ($custom) {
            $form->get('custom')->setValue($custom);
        }
        $view = new ViewModel();

        $form->get('form-actions')->get('confirm')->setLabel($confirmBtnLabel);
        $form->get('form-actions')->get('cancel')->setLabel($cancelBtnLabel);

        $view->setVariable('form', $form);
        $view->setVariable('label', $label);
        if ($setTerminal) {
            $view->setTerminal(true);
        }
        $view->setTemplate('pages/confirm');

        return $view;
    }
}
