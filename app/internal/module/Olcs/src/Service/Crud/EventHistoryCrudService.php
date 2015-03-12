<?php

/**
 * Event History Crud Service
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Admin\Service\Crud;

use Zend\Form\Form;
use Common\Service\Crud\AbstractCrudService;
use Common\Service\Crud\GenericProcessFormInterface;

/**
 * Event History Crud Service
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class EventHistoryCrudService extends AbstractCrudService
{
    /**
     * Get a populated, filtered, ordered table
     *
     * @return TableBuilder
     */
    public function getList()
    {


        return $this->getServiceLocator()->get('Table')
            ->prepareTable('event-history', $this->getTableData());
    }

    /**
     * Grab the table data from the entity service
     *
     * @param array $params
     * @return array
     */
    protected function getTableData(array $params)
    {
        return $this->getServiceLocator()
                    ->get('DataServiceManager')
                    ->get('Olcs\Service\Data\EventHistory')
                    ->fetchList($params);
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
        return false;
    }

    /**
     * Handle an individual deletion
     *
     * @param int $id
     */
    protected function delete($id)
    {
        //
    }
}
