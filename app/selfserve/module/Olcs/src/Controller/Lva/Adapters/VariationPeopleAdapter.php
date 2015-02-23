<?php

/**
 * External Variation People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
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
    protected $lva = 'variation';

    public function alterFormForOrganisation(Form $form, $table, $orgId, $orgType)
    {
        if (!$this->isExceptionalType($orgType)) {
            return;
        }

        return $this->getServiceLocator()->get('Lva\People')->lockOrganisationForm($form, $table, $orgId);
    }

    public function alterAddOrEditFormForOrganisation(Form $form, $orgId, $orgType)
    {
        if (!$this->isExceptionalType($orgType)) {
            return;
        }

        return $this->getServiceLocator()->get('Lva\People')->lockPersonForm($form, $orgType);
    }

    public function canModify($orgId)
    {
        // i.e. they *can't* modify exceptional org types
        // but can modify all others
        return $this->isExceptionalOrganisation($orgId) === false;
    }

    /**
     * @TODO all methods below duped across int/ext variation adapters
     */
    protected function getTableConfig($orgId)
    {
        if ($this->isExceptionalOrganisation($orgId)) {
            return 'lva-people';
        }

        return 'lva-variation-people';
    }

    public function attachMainScripts()
    {
        // @TODO switch based on exceptional type or not
        $this->getServiceLocator()->get('Script')->loadFile('lva-crud-delta');
    }

    /**
     * Extend the abstract behaviour to get the table data for the main form
     *
     * @return array
     */
    protected function getTableData($orgId)
    {
        if ($this->isExceptionalOrganisation($orgId)) {
            return parent::getTableData($orgId);
        }

        $appId = $this->getVariationAdapter()->getIdentifier();

        $data = $this->getServiceLocator()
            ->get('Lva\VariationPeople')
            ->getTableData($orgId, $appId);

        return $this->tableData = $this->formatTableData($data);
    }

    public function delete($orgId, $id)
    {
        if ($this->isExceptionalOrganisation($orgId)) {
            return parent::save($orgId, $data);
        }

        $appId = $this->getLvaAdapter()->getIdentifier();

        return $this->getServiceLocator()
            ->get('Lva\VariationPeople')
            ->deletePerson($orgId, $id, $appId);
    }

    public function restore($orgId, $id)
    {
        if ($this->isExceptionalOrganisation($orgId)) {
            return parent::restore($orgId, $id);
        }

        $appId = $this->getLvaAdapter()->getIdentifier();

        return $this->getServiceLocator()
            ->get('Lva\VariationPeople')
            ->restorePerson($orgId, $id, $appId);
    }

    public function save($orgId, $data)
    {
        if ($this->isExceptionalOrganisation($orgId)) {
            return parent::save($orgId, $data);
        }

        $appId = $this->getLvaAdapter()->getIdentifier();

        return $this->getServiceLocator()
            ->get('Lva\VariationPeople')
            ->savePerson($orgId, $id, $appId);
    }
}
