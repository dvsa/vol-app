<?php

namespace Olcs\FormService\Form\Lva;

use Common\FormService\Form\Lva\Addresses as CommonAddresses;

/**
 * Addresses Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Addresses extends CommonAddresses
{
    private static $tableConfigName = 'lva-phone-contacts';

    /**
     * Alter Form
     *
     * @param \Zend\Form\Form $form   Form
     * @param array           $params Params
     *
     * @return void
     */
    protected function alterForm(\Zend\Form\Form $form, array $params)
    {
        $form->get('form-actions')->get('save')->setLabel('internal.save.button');

        $this->removeEstablishment($form, $params['typeOfLicence']['licenceType']);

        //  fill table
        $table = $this->getFormServiceLocator()->getServiceLocator()->get('Table')
            ->prepareTable(self::$tableConfigName, ($params['corrPhoneContacts'] ?: []));

        $this->getFormHelper()->populateFormTable($form->get('phoneContactsTable'), $table);

        //  remove phones fields
        /** @var \Zend\Form\Element $field */
        /** @var \Zend\Form\Fieldset $contactFieldset */
        $contactFieldset = $form->get('contact');
        foreach ($contactFieldset as $field) {
            $fldName = $field->getName();
            if ($fldName !== 'email') {
                $this->getFormHelper()->remove($form, 'contact->' . $fldName);
            }
        }

        $contactFieldset->setOptions([]);
        $contactFieldset->setLabel('');

        //  change email settings
        /** @var \Zend\InputFilter\Input $emailElm */
        $emailElm = $form->getInputFilter()->get('contact')->get('email');
        $emailElm
            ->setRequired(false)
            ->setAllowEmpty(true);
    }
}
