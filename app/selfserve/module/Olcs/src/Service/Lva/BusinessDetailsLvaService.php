<?php

/**
 * Business Details LVA service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Service\Lva;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Form\Form;

/**
 * Business Details LVA service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BusinessDetailsLvaService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function lockDetails(Form $form)
    {
        $fieldset = $form->get('data');

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $formHelper->lockElement($fieldset->get('companyNumber'), 'gutted');
        $formHelper->lockElement($fieldset->get('name'), 'gutted');

        $formHelper->disableElement($form, 'data->companyNumber->company_number');
        $formHelper->disableElement($form, 'data->companyNumber->submit_lookup_company');
        $formHelper->disableElement($form, 'data->name');
    }
}
