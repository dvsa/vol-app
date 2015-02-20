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
                $this->indexRows(self::SOURCE_ORGANISATION, $orgPeople),
                $this->indexRows(self::SOURCE_APPLICATION, $applicationPeople)
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

        foreach ($orgData as $id => $row) {

            if (!isset($applicationData[$id])) {

                // E for existing (No updates)
                $row['action'] = self::ACTION_EXISTING;
                $data[] = $row;
            } elseif ($applicationData[$id]['action'] === self::ACTION_UPDATED) {
                // If we have updated the operating centre

                $row['action'] = self::ACTION_CURRENT;
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

    public function delete($orgId)
    {
        $id = $this->getController()->params('child_id');

        $appId = $this->getLvaAdapter()->getIdentifier();

        $appPerson = $this->getServiceLocator()->get('Entity\ApplicationOrganisationPerson')
            ->getByApplicationAndPersonId($appId, $id);

        if ($appPerson) {
            var_dump($appPerson); die();
            $this->getServiceLocator()->get('Entity\ApplicationOrganisationPerson')
                ->delete($appPerson['id']); die();
            return $this->getEntityService()->delete($id);
        }

        // must be an org one then....
        $this->getServiceLocator()->get('Entity\OrganisationPerson')
            ->variationDelete($id, $orgId, $appId);
    }

    public function restore($orgId)
    {
        $id = $this->getController()->params('child_id');

        // @TODO methodize
        $data = $this->getTableData($orgId);
        foreach ($data as $row) {
            if ($row['id'] == $id) {
                $action = $row['action'];
                break;
            }
        }

        if (in_array($action, [self::ACTION_DELETED])) {

            $appId = $this->getLvaAdapter()->getIdentifier();

            // @TODO: clean up. At least cache the service...
            $appPerson = $this->getServiceLocator()->get('Entity\ApplicationOrganisationPerson')
                ->getByApplicationAndPersonId($appId, $id);

            $this->getServiceLocator()->get('Entity\ApplicationOrganisationPerson')
                ->delete($appPerson['id']);

            return $this->getController()->redirect()
                ->toRouteAjax(null, ['action' => null, 'child_id' => null], [], true);
        }

        throw new \Exception('Can\'t restore this record');
    }

    // @TODO straightforward 'add'
    //
    // @TODO update which is against an existing record (new record and delete)
    //
    // @TODO update which is against a new record
}
