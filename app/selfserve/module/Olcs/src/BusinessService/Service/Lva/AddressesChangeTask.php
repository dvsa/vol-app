<?php

/**
 * Addresses Change Task
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\BusinessService\Service\Lva;

use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\BusinessServiceAwareInterface;
use Common\BusinessService\BusinessServiceAwareTrait;
use Common\Service\Data\CategoryDataService;

/**
 * Addresses Change Task
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class AddressesChangeTask implements BusinessServiceInterface, BusinessServiceAwareInterface
{
    use BusinessServiceAwareTrait;

    /**
     * Processes the data by passing it through a number of business rules and then persisting it
     *
     * @param array $params
     * @return Common\BusinessService\ResponseInterface
     */
    public function process(array $params)
    {
        $taskParams = [
            'category' => CategoryDataService::CATEGORY_APPLICATION,
            'subCategory' => CategoryDataService::TASK_SUB_CATEGORY_APPLICATION_ADDRESS_CHANGE_DIGITAL,
            'description' => 'Address Change',
            'licence' => $params['licenceId']
        ];

        return $this->getBusinessServiceManager()->get('Task')->process($taskParams);
    }
}
