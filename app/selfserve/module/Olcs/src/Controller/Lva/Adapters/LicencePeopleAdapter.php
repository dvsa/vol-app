<?php

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Olcs\Controller\Lva\Adapters;


use Zend\Form\Form;
use Common\Controller\Lva\Adapters\AbstractPeopleAdapter;

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicencePeopleAdapter extends AbstractPeopleAdapter
{
    /**
     * Alter form depending on type of organisation
     *
     * @param \Zend\Form\Form                    $form  form
     * @param \Common\Service\Table\TableBuilder $table table
     *
     * @return mixed
     */
    public function alterFormForOrganisation(Form $form, $table)
    {
        return $this->getServiceLocator()->get('Lva\People')->lockOrganisationForm($form, $table);
    }

    /**
     * Change the Add/Edit buttons based on organisation
     *
     * @param \Zend\Form\Form $form form
     *
     * @return mixed
     */
    public function alterAddOrEditFormForOrganisation(Form $form)
    {
        return $this->getServiceLocator()->get('Lva\People')->lockPersonForm($form, $this->getOrganisationType());
    }

    /**
     * Determine if form can be modified
     *
     * @return bool
     */
    public function canModify()
    {
        return false;
    }

    /**
     * Create the table with added button for adding person
     *
     * @return \Common\Service\Table\TableBuilder
     *
     */
    public function createTable()
    {
        $table = parent::createTable();

        $table->setSetting(
            'crud',
            [
                'actions' => [
                    'add' => [
                        'label' => parent::getAddLabelTextForOrganisation()
                    ]
                ]
            ]
        );
        return $table;
    }
}
