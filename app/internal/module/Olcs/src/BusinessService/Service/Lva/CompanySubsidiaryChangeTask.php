<?php

/**
 * Company Subsidiary Change Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\BusinessService\Service\Lva;

use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\Response;

/**
 * Company Subsidiary Change Task
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CompanySubsidiaryChangeTask implements BusinessServiceInterface
{
    /**
     * Processes the data by passing it through a number of business rules and then persisting it
     *
     * @param array $params
     * @return Common\BusinessService\ResponseInterface
     */
    public function process(array $params)
    {
        // No op
        $response = new Response();
        $response->setType(Response::TYPE_NO_OP);
        return $response;
    }
}
