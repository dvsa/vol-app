<?php

/**
 * Inspection Request Update
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\BusinessService\Service;

use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\Response;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\Service\Entity\InspectionRequestEntityService;
use Olcs\View\Model\Email\InspectionRequest as InspectionRequestEmailViewModel;

/**
 * Inspection Request Update
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class InspectionRequestUpdate implements BusinessServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Process an Inspection Request status update
     *
     * @todo
     *
     * @param array $params
     * @return Common\BusinessService\ResponseInterface
     */
    public function process(array $params)
    {
        return new Response(Response::TYPE_SUCCESS);
    }
}
