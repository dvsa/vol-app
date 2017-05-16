<?php

namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\AbstractPeopleAdapter;

/**
 * External Variation People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VariationPeopleAdapter extends AbstractPeopleAdapter
{
    /**
     * Can Modify
     *
     * @return bool
     */
    public function canModify()
    {
        // i.e. they *can't* modify exceptional org types
        // but can modify all others
        return $this->isExceptionalOrganisation() === false;
    }

    /**
     * Get Table Config
     *
     * @return string
     */
    protected function getTableConfig()
    {
        if (!$this->useDeltas()) {
            return 'lva-people';
        }
        return 'lva-variation-people';
    }

    /**
     * Alter Form For Organisation
     *
     * @param \Zend\Form\FormInterface           $form  Form
     * @param \Common\Service\Table\TableBuilder $table Table
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
     * Alter Add Or Edit Form For Organisation
     *
     * @param Form $form Form
     *
     * @return void
     */
    public function alterAddOrEditFormForOrganisation(Form $form)
    {
        if ($this->canModify()) {
            return;
        }

        $this->getServiceLocator()->get('Lva\People')->lockPersonForm($form, $this->getOrganisationType());
    }

    /**
     * Get the backend command to create a Person
     *
     * @param array $params Params
     *
     * @return \Dvsa\Olcs\Transfer\Command\AbstractCommand
     */
    protected function getCreateCommand($params)
    {
        $params['id'] = $this->getApplicationId();
        return \Dvsa\Olcs\Transfer\Command\Application\CreatePeople::create($params);
    }

    /**
     * Get the backend command to update a Person
     *
     * @param array $params Params
     *
     * @return \Dvsa\Olcs\Transfer\Command\AbstractCommand
     */
    protected function getUpdateCommand($params)
    {
        $params['person'] = $params['id'];
        $params['id'] = $this->getApplicationId();
        return \Dvsa\Olcs\Transfer\Command\Application\UpdatePeople::create($params);
    }

    /**
     * Get the backend command to delete a Person
     *
     * @param array $params Params
     *
     * @return \Dvsa\Olcs\Transfer\Command\AbstractCommand
     */
    protected function getDeleteCommand($params)
    {
        $params['id'] = $this->getApplicationId();
        return \Dvsa\Olcs\Transfer\Command\Application\DeletePeople::create($params);
    }
}
