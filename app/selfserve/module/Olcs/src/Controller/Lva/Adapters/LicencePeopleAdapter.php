<?php

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Olcs\Controller\Lva\Adapters;

use Common\Controller\Lva\Adapters\AbstractPeopleAdapter;
use Common\Service\Table\TableBuilder;
use Zend\Form\Form;

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicencePeopleAdapter extends AbstractPeopleAdapter
{
    /**
     * Alter Form For Organisation
     *
     * @param Form         $form  Form
     * @param TableBuilder $table Table
     *
     * @return void
     */
    public function alterFormForOrganisation(Form $form, $table)
    {
        if ($this->canModify()) {
            parent::alterFormForOrganisation($form, $table);
            return;
        }

        $this->getServiceLocator()->get('Lva\People')->lockOrganisationForm($form, $table);
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
        return !$this->isExceptionalOrganisation();
    }

    /**
     * Create the table with added button for adding person
     *
     * @return TableBuilder
     *
     */
    public function createTable()
    {
        $table = parent::createTable();
        return parent::amendLicencePeopleListTable($table);
    }
}
