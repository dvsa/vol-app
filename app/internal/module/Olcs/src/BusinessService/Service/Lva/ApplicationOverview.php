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

/**
 * Application Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationOverview implements
    BusinessServiceInterface,
    ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

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
        $this->getServiceLocator()->get('Entity\Application')
            ->save($params['details']);

        // persist the Lead Traffic Area which is a property of the organisation
        $applicationData = $this->getServiceLocator()->get('Entity\Application')
            ->getOverview($params['details']);

        $this->getServiceLocator()->get('Entity\Organisation')->forceUpdate(
            $applicationData['licence']['organisation']['id'],
            [
                'leadTcArea' => $params['details']['leadTcArea'],
            ]
        );

        return new Response(Response::TYPE_SUCCESS);
    }
}
