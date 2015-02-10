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

    public function alterFormForLva(Form $form, $orgId, $lvaType)
    {
        // @FIXME: split into adapters
        if (
            $lvaType !== 'application'
            || $this->getServiceLocator()->get('Entity\Organisation')->hasInForceLicences($orgId)
        ) {
            $this->lockType($form);
        }
    }

    public function lockType(Form $form)
    {
        $element = $form->get('data')->get('type');

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $formHelper->lockElement($element, 'business-type.locked');

        $formHelper->disableElement($form, 'data->type');
    }

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
