<?php

/**
 * External Variation People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Adapters\AbstractPeopleAdapter;
use Common\Service\Entity\OrganisationEntityService;

/**
 * External Variation People Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VariationPeopleAdapter extends AbstractPeopleAdapter
{
    protected $tableConfig = 'lva-variation-people';
    protected $lva = 'variation';

    const ACTION_UPDATED = 'U';
    const ACTION_EXISTING = 'E';
    const ACTION_CURRENT = 'C';

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

    public function attachMainScripts()
    {
        $this->getServiceLocator()->get('Script')->loadFile('lva-crud-delta');
    }

    /**
     * Extend the abstract behaviour to get the table data for the main form
     *
     * @return array
     */
    protected function getTableData($orgId)
    {
        if (empty($this->tableData)) {

            $orgPeople = $this->getServiceLocator()->get('Entity\Person')
                ->getAllForOrganisation($orgId)['Results'];

            $applicationPeople = $this->getServiceLocator()->get('Entity\Person')
                ->getAllForApplication($this->getVariationAdapter()->getIdentifier())['Results'];

            $data = $this->updateAndFilterTableData(
                $this->indexRows('O', $orgPeople),
                $this->indexRows('A', $applicationPeople)
            );

            $this->tableData = $this->formatTableData($data);
        }

        return $this->tableData;
    }

    /**
     * Update and filter the table data for variations
     *
     * @param array $orgData
     * @param array $applicationData
     * @return array
     */
    protected function updateAndFilterTableData($orgData, $applicationData)
    {
        $data = array();

        foreach ($orgData as $ocId => $row) {

            if (!isset($applicationData[$ocId])) {
                // If we have no application oc record

                // E for existing (No updates)
                $row['person']['action'] = self::ACTION_EXISTING;
                $data[] = $row;
            } elseif ($applicationData[$ocId]['person']['action'] === self::ACTION_UPDATED) {
                // If we have updated the operating centre

                $row['person']['action'] = self::ACTION_CURRENT;
                $data[] = $row;
            }
        }

        $data = array_merge($data, $applicationData);

        return $data;
    }

    private function indexRows($key, $data)
    {
        $indexed = [];

        foreach ($data as $value) {
            $id = $value['person']['id'];
            $value['person']['source'] = $key;
            $indexed[$id] = $value;
        }

        return $indexed;
    }

    public function getPerson($id)
    {
        $details = $this->getServiceLocator()
            ->get('Entity\ApplicationOrganisationPerson')->getByPersonId($id);

        if (!$details) {
            $details = $this->getServiceLocator()
                ->get('Entity\OrganisationPerson')->getByPersonId($id);
        }

        return $details['person'];
    }
}
