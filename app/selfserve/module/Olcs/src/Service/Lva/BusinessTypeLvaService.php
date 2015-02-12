<?php

/**
 * Business Type LVA service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Service\Lva;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Form\Form;

/**
 * Business Type LVA service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BusinessTypeLvaService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function lockType(Form $form)
    {
        $element = $form->get('data')->get('type');

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $formHelper->lockElement($element, 'business-type.locked');

        $formHelper->disableElement($form, 'data->type');
    }
}
