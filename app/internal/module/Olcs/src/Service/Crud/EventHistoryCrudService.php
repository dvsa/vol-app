<?php

/**
 * Event History Crud Service
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Service\Crud;

use Zend\Form\Form as ZendForm;
use Common\Service\Crud\AbstractCrudService;
use Common\Service\Crud\GenericProcessFormInterface;
use Common\Crud\RetrieveInterface;
use Common\Service\Table\TableBuilder;

/**
 * Event History Crud Service
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class EventHistoryCrudService extends AbstractCrudService implements
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
        throw new \LogicException('There is no implementation for a single record in Event History.');
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
        return $this->getServiceLocator()->get('DataServiceManager')
            ->get('Generic\Service\Data\EventHistory')->fetchList($criteria);
    }

    /**
     * Gets a populated table object.
     *
     * @param array $criteria Search / request criteria.
     *
     * @return \Common\Service\Table\TableBuilder
     */
    /*public function getTable(array $criteria = null)
    {
        return $this->getServiceLocator()->get('Table')->prepareTable('event-history', $this->getList($criteria));
    }*/


    /**
     * Grab the table data from the entity service
     *
     * @param array $params
     * @return array
     */
    protected function getTableData(array $params)
    {
        return;
    }

    /**
     * Check if a form is valid
     *
     * @param ZendForm $form
     * @param int|null $id
     * @return boolean
     */
    public function isFormValid(ZendForm $form, $id = null)
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
