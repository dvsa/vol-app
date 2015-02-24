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

    public function canModify($orgId)
    {
        // i.e. they *can't* modify exceptional org types
        // but can modify all others
        return $this->isExceptionalOrganisation($orgId) === false;
    }

    protected function getTableConfig($orgId)
    {
        if ($this->doesNotRequireDeltas($orgId)) {
            return 'lva-people';
        }

        return 'lva-variation-people';
    }

    /**
     * Extend the abstract behaviour to get the table data for the main form
     *
     * @return array
     */
    protected function getTableData($orgId)
    {
        if ($this->doesNotRequireDeltas($orgId)) {
            return parent::getTableData($orgId);
        }

        $appId = $this->getVariationAdapter()->getIdentifier();

        $data = $this->getServiceLocator()
            ->get('Lva\VariationPeople')
            ->getTableData($orgId, $appId);

        return $this->tableData = $this->formatTableData($data);
    }

    public function alterFormForOrganisation(Form $form, $table, $orgId)
    {
        if (!$this->isExceptionalOrganisation($orgId)) {
            return;
        }

        return $this->getServiceLocator()->get('Lva\People')->lockOrganisationForm($form, $table);
    }

    public function alterAddOrEditFormForOrganisation(Form $form, $orgId)
    {
        if (!$this->isExceptionalOrganisation($orgId)) {
            return;
        }

        return $this->getServiceLocator()->get('Lva\People')->lockPersonForm(
            $form,
            $this->getOrganisationType($orgId)
        );
    }

    public function delete($orgId, $id)
    {
        if ($this->doesNotRequireDeltas($orgId)) {
            return parent::delete($orgId, $id);
        }

        $appId = $this->getLvaAdapter()->getIdentifier();

        return $this->getServiceLocator()
            ->get('Lva\VariationPeople')
            ->deletePerson($orgId, $id, $appId);
    }

    public function restore($orgId, $id)
    {
        if ($this->doesNotRequireDeltas($orgId)) {
            return parent::restore($orgId, $id);
        }

        $appId = $this->getLvaAdapter()->getIdentifier();

        return $this->getServiceLocator()
            ->get('Lva\VariationPeople')
            ->restorePerson($orgId, $id, $appId);
    }

    public function save($orgId, $data)
    {
        if ($this->doesNotRequireDeltas($orgId)) {
            return parent::save($orgId, $data);
        }

        $appId = $this->getLvaAdapter()->getIdentifier();

        return $this->getServiceLocator()
            ->get('Lva\VariationPeople')
            ->savePerson($orgId, $data, $appId);
    }

    public function getPersonPosition($orgId, $personId)
    {
        if ($this->doesNotRequireDeltas($orgId)) {
            return parent::getPersonPosition($orgId, $personId);
        }

        $appId = $this->getLvaAdapter()->getIdentifier();

        return $this->getServiceLocator()
            ->get('Lva\VariationPeople')
            ->getPersonPosition($orgId, $appId, $personId);
    }

    protected function doesNotRequireDeltas($orgId)
    {
        return $this->isExceptionalOrganisation($orgId);
    }
}
