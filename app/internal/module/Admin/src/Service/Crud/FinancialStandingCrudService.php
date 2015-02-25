<?php

/**
 * Financial Standing Crud Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Admin\Service\Crud;

use Common\Util\Redirect;
use Zend\Http\Request;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Common\Service\Crud\CrudServiceInterface;
use Common\Service\Crud\GenericProcessFormInterface;

/**
 * Financial Standing Crud Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialStandingCrudService implements
    ServiceLocatorAwareInterface,
    CrudServiceInterface,
    GenericProcessFormInterface
{
    use ServiceLocatorAwareTrait;

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
     * Process an Add/Edit form
     *
     * @param Request $request
     * @param int $id
     */
    public function processForm(Request $request, $id = null)
    {
        return $this->getServiceLocator()->get('Crud\Generic')->processForm($this, $request, $id);
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
     * Get the default form data, when we are adding without post data
     *
     * @return array
     */
    public function getDefaultFormData()
    {
        return [
            'details' => [
                'effectiveFrom' => $this->getServiceLocator()->get('Helper\Date')->getDate()
            ]
        ];
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

        $data = [
            'details' => $this->getServiceLocator()->get('Helper\Data')->replaceIds($record)
        ];

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
}
