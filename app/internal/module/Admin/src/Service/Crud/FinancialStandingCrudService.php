<?php

/**
 * Financial Standing Crud Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Admin\Service\Crud;

use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 * Financial Standing Crud Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialStandingCrudService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function getList()
    {
        return $this->getServiceLocator()->get('Table')
            ->prepareTable('admin-financial-standing', $this->getTableData());
    }

    protected function getTableData()
    {
        return $this->getServiceLocator()->get('Entity\FinancialStandingRate')->getFullList();
    }
}
