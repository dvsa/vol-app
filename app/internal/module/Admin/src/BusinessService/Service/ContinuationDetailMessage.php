<?php

/**
 * Continuation Detail Message
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Admin\BusinessService\Service;

use Common\BusinessService\BusinessServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\BusinessService\Response;

/**
 * Continuation Detail Message
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ContinuationDetailMessage implements BusinessServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Updated the continuation detail records, add the messages to the queue
     *
     * @param array $params
     * @return ResponseInterface
     */
    public function process(array $params)
    {
        if (empty($params['ids'])) {
            $response = new Response(Response::TYPE_RULE_FAILED);
            $response->setMessage('Please select one or more licence(s) to be queued');
            return $response;
        }

        $this->getServiceLocator()->get('Entity\ContinuationDetail')
            ->generateChecklists($params['ids']);

        $response = new Response(Response::TYPE_SUCCESS);
        return $response;
    }
}
