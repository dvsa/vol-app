<?php

/**
 * External Licence & Variation Business Details Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Adapters;

use Zend\Form\Form;
use Common\Controller\Lva\Interfaces\BusinessDetailsAdapterInterface;
use Common\Controller\Lva\Adapters\AbstractAdapter;
use Common\Service\Data\CategoryDataService;

/**
 * External Licence & Variation Business Details Adapter
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceVariationBusinessDetailsAdapter extends AbstractAdapter implements BusinessDetailsAdapterInterface
{
    private $actionMap = [
        'edit'   => 'updated',
        'add'    => 'added',
        'delete' => 'deleted'
    ];

    public function alterFormForOrganisation(Form $form, $orgId)
    {
        $this->getServiceLocator()->get('Lva\BusinessDetails')->lockDetails($form);
    }

    public function postSave($data)
    {
        return $this->createTask(
            [
                // @TODO: confirm sub category
                'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_HEARINGS_APPEALS,
                'description' => 'Change to business details',
                'createdBy' => $data['user'],
                'lastModifiedBy' => $data['user']
            ]
        );

    }

    public function postCrudSave($data)
    {
        $action = $this->actionMap[$data['mode']];

        return $this->createTask(
            [
                'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_SUBSIDIARY_DIGITAL,
                'description' => 'Subsidiary company ' . $action . ' - ' . $data['name'],
                'createdBy' => $data['user'],
                'lastModifiedBy' => $data['user']
            ]
        );
    }

    private function createTask($data)
    {
        $currentDate = $this->getServiceLocator()->get('Helper\Date')->getDate('Y-m-d H:i:s');

        $taskData = array_merge(
            [
                'category' => CategoryDataService::CATEGORY_APPLICATION,
                'actionDate' => $currentDate,
                'owner' => 1,
                'team' => 2,
                'lastModifiedOn' => $currentDate
            ],
            $data
        );

        $this->getServiceLocator()
            ->get('Entity\Task')
            ->save($taskData);
    }
}
