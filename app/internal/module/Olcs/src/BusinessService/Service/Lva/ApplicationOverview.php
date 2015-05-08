<?php

/**
 * Application Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\BusinessService\Service\Lva;

use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\Response;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\BusinessRule\BusinessRuleAwareInterface;
use Common\BusinessRule\BusinessRuleAwareTrait;

/**
 * Application Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationOverview implements
    BusinessServiceInterface,
    ServiceLocatorAwareInterface,
    BusinessRuleAwareInterface
{
    use ServiceLocatorAwareTrait,
        BusinessRuleAwareTrait;

    /**
     * Formats application and tracking data from the overview form and persists
     * changes
     *
     * @param array $params
     * @return Common\BusinessService\ResponseInterface
     */
    public function process(array $params)
    {
        // persist tracking data
        $this->getServiceLocator()->get('Entity\ApplicationTracking')
            ->save($params['tracking']);

        // persist application data
        $appRule = $this->getBusinessRuleManager()->get('ApplicationOverview');
        $applicationData = $appRule->filter($params['details']);
        $this->getServiceLocator()->get('Entity\Application')
            ->save($applicationData);

        // persist the Lead Traffic Area which is a property of the organisation
        $applicationOverview = $this->getServiceLocator()->get('Entity\Application')
            ->getOverview($params['details']['id']);
        $this->getServiceLocator()->get('Entity\Organisation')->forceUpdate(
            $applicationOverview['licence']['organisation']['id'],
            [
                'leadTcArea' => $params['details']['leadTcArea'],
            ]
        );

        if (isset($params['details']['translateToWelsh'])) {
            // Persist the translate to welsh which is a property of the licence.
            $this->getServiceLocator()->get('Entity\Licence')->forceUpdate(
                $applicationOverview['licence']['id'],
                [
                    'translateToWelsh' => $params['details']['translateToWelsh'],
                ]
            );
        }

        return new Response(Response::TYPE_SUCCESS);
    }
}
