<?php

/**
 * Business Details LVA service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Service\Lva;

use Common\Service\Data\CategoryDataService;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Form\Form;

/**
 * Business Details LVA service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class BusinessDetailsLvaService implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function lockDetails(Form $form)
    {
        $fieldset = $form->get('data');

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $formHelper->lockElement($fieldset->get('companyNumber'), 'business-details.company_number.locked');
        $formHelper->lockElement($fieldset->get('name'), 'business-details.name.locked');

        $formHelper->disableElement($form, 'data->companyNumber->company_number');
        $formHelper->disableElement($form, 'data->companyNumber->submit_lookup_company');
        $formHelper->disableElement($form, 'data->name');
    }

    public function createChangeTask($data)
    {
        return $this->createTask(
            [
                // @TODO: confirm sub category
                'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_HEARINGS_APPEALS,
                'description' => 'Change to business details',
                'createdBy' => $data['user'],
                'lastModifiedBy' => $data['user'],
                'licence' => $data['licence']
            ]
        );
    }

    public function createSubsidiaryChangeTask($action, $data)
    {
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
