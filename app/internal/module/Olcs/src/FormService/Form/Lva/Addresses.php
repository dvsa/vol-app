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
    private $tableConfigName = 'lva-phone-contacts';

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
            ->prepareTable(
                $this->tableConfigName,
                ($params['apiData']['correspondenceCd']['phoneContacts'] ?: [])
            );

        $this->getFormHelper()->populateFormTable($form->get('phoneContactsTable'), $table);

        //  remove phone fields
        $this->getFormHelper()
            ->remove($form, 'contact->phone-validator')
            ->remove($form, 'contact');
    }
}
