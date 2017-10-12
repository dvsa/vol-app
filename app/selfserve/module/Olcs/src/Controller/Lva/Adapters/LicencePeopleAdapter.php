<?php

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Common\Service\Table\TableBuilder;
use Zend\Form\Form;
use Common\Controller\Lva\Adapters\AbstractPeopleAdapter;

/**
 * External Licence People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicencePeopleAdapter extends AbstractPeopleAdapter
{
    public function alterFormForOrganisation(Form $form, $table)
    {
        return $this->getServiceLocator()->get('Lva\People')->lockOrganisationForm($form, $table);
    }

    public function alterAddOrEditFormForOrganisation(Form $form)
    {
        return $this->getServiceLocator()->get('Lva\People')->lockPersonForm($form, $this->getOrganisationType());
    }

    public function canModify()
    {
        return false;
    }

    public function createTable()
    {
        $table = parent::createTable();

        $table->setSetting('crud', [
            'actions' => [
                'add' => [
                    'label' => 'blah'
                ]
            ]
        ]);

        return $table;
    }
}
