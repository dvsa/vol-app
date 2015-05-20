<?php

/**
 * Companies House Alert Crud Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Admin\Service\Crud;

use Common\Crud\RetrieveInterface;
use Zend\Form\Form;
use Common\Util\Redirect;
use Common\Service\Crud\AbstractCrudService;
use Common\Service\Crud\GenericProcessFormInterface;

/**
 * Companies House Alert Crud Service
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompaniesHouseAlertCrudService extends AbstractCrudService implements
    GenericProcessFormInterface,
    RetrieveInterface
{
    /**
     * Get's one single record.
     *
     * @param $id
     *
     * @return mixed
     */
    public function get($id)
    {
        throw new \LogicException('There is no implementation for a single Companies House Alert');
    }

    /**
     * Gets a list of records matching criteria.
     *
     * @param array $criteria Search / request criteria.
     *
     * @return array|null
     */
    public function getList(array $criteria = null)
    {
        return $this->getServiceLocator()->get('Entity\CompaniesHouseAlert')->getList([]);
    }

    /**
     * Check if a form is valid
     *
     * @param Form $form
     * @param int|null $id
     * @return boolean
     */
    public function isFormValid(Form $form, $id = null)
    {
        return $form->isValid();
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

        $this->getServiceLocator()->get('Entity\CompaniesHouseAlert')->save($record);
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

        $record = $this->getServiceLocator()->get('Entity\CompaniesHouseAlert')->getRecordById($id);

        return ['details' => $this->getServiceLocator()->get('Helper\Data')->replaceIds($record)];
    }

    /**
     * Grab the built/configured form
     *
     * @return \Zend\Form\Form
     */
    public function getForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')->createForm('CompaniesHouseAlert');
    }

    /**
     * Grab the table data from the entity service
     *
     * @deprecated Not needed, superseded by getList()
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
        $this->getServiceLocator()->get('Entity\CompaniesHouseAlert')->delete($id);
    }
}
