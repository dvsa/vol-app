<?php

/**
 * GracePeriod.php
 */
namespace Olcs\BusinessService\Service\Lva;

use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\Response;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class GracePeriod
 *
 * Process and save a grace period.
 *
 * @package Olcs\BusinessService\Service\Lva
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class GracePeriod implements BusinessServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Process and save a grace period.
     *
     * @param array $params
     *
     * @return Response
     */
    public function process(array $params)
    {
        $gracePeriodEntityService = $this->getServiceLocator()
            ->get('Entity\GracePeriod');

        if (isset($params['id'])) {
            $gracePeriodEntityService->update($params['id'], $params);
            return new Response(Response::TYPE_SUCCESS);
        }

        $gracePeriodEntityService->save($params);
        return new Response(Response::TYPE_SUCCESS);
    }
}
