<?php

/**
 * Financial Standing Crud Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Admin\Service\Crud;

use Zend\Form\Form;
use Common\Util\Redirect;
use Common\Service\Crud\AbstractCrudService;
use Common\Service\Crud\GenericProcessFormInterface;

/**
 * Financial Standing Crud Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialStandingCrudService extends AbstractCrudService implements GenericProcessFormInterface
{
    /**
     * Get a populated, filtered, ordered table
     *
     * @return TableBuilder
     */
    public function getList()
    {
        return $this->getServiceLocator()->get('Table')
            ->prepareTable('admin-financial-standing', $this->getTableData());
    }

    /**
     * Check if a form is valid
     *
     * @param Form $form
     * @return boolean
     */
    public function isFormValid(Form $form, $id = null)
    {
        if ($form->isValid()) {

            $data = $form->getData();

            if ($this->canAdd($data['details'], $id)) {
                return true;
            }

            $this->getServiceLocator()->get('Helper\FlashMessenger')
                ->addErrorMessage('financial-standing-already-exists-validation');

            return false;
        }

        return false;
    }

    /**
     * Process the saving of an entity
     *
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function processSave($data, $id = null)
    {
        $record = $data['details'];

        if (isset($id)) {
            $record['id'] = $id;
        } else {
            unset($record['version']);
        }

        $this->getServiceLocator()->get('Entity\FinancialStandingRate')->save($record);
        $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('record-saved-successfully');

        $redirect = new Redirect();
        return $redirect->toRouteAjax(null);
    }

    /**
     * Get an entities data by an id
     *
     * @param int $id
     * @return array|null
     */
    public function getRecordData($id)
    {
        if (empty($id)) {
            return null;
        }

        $record = $this->getServiceLocator()->get('Entity\FinancialStandingRate')->getRecordById($id);

        $data = ['details' => $this->getServiceLocator()->get('Helper\Data')->replaceIds($record)];

        return $data;
    }

    /**
     * Grab the built/configured form
     *
     * @return \Zend\Form\Form
     */
    public function getForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')->createForm('FinancialStandingRate');
    }

    /**
     * Grab the table data from the entity service
     *
     * @return array
     */
    protected function getTableData()
    {
        return $this->getServiceLocator()->get('Entity\FinancialStandingRate')->getFullList();
    }

    /**
     * Handle an individual deletion
     *
     * @param int $id
     */
    protected function delete($id)
    {
        $this->getServiceLocator()->get('Entity\FinancialStandingRate')->delete($id);
    }

    protected function canAdd($data, $id = null)
    {
        $query = [
            'goodsOrPsv' => $data['goodsOrPsv'],
            'licenceType' => $data['licenceType'],
            'effectiveFrom' => $data['effectiveFrom']
        ];

        $results = $this->getServiceLocator()->get('Entity\FinancialStandingRate')
            ->getList($query)['Results'];

        // Unset the current record, so we can count the others
        foreach ($results as $key => $row) {
            if ($row['id'] === $id) {
                unset($results[$key]);
                break;
            }
        }

        return empty($results);
    }
}
