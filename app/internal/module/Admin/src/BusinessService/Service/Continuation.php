<?php

/**
 * Continuation
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Admin\BusinessService\Service;

use Common\BusinessService\BusinessServiceInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\BusinessService\Response;
use Common\Service\Entity\ContinuationDetailEntityService;

/**
 * Continuation
 * @todo remove
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Continuation implements BusinessServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Create a continuation record, and all related continuation detail records
     *
     * @param array $params
     * @return ResponseInterface
     */
    public function process(array $params)
    {
        $licenceService = $this->getServiceLocator()->get('Entity\Licence');

        $licences = $licenceService->findForContinuationCriteria($params['data']);

        // If there are no licences, then we don't need to do anything
        // but we do need to tell the controller that we didn't do anything
        // so we return a no_op
        if (empty($licences)) {
            return new Response(Response::TYPE_NO_OP);
        }

        $continuationService = $this->getServiceLocator()->get('Entity\Continuation');

        try {
            $saved = $continuationService->save($params['data']);
        } catch (\Exception $ex) {
            return $this->failedToCreateContinuation();
        }

        if (!isset($saved['id'])) {
            return $this->failedToCreateContinuation();
        }

        $continuationDetailService = $this->getServiceLocator()->get('Entity\ContinuationDetail');

        $continuationDetails = [];

        foreach ($licences as $licence) {
            $continuationDetails[] = [
                'licence' => $licence['id'],
                'received' => 'N',
                'status' => ContinuationDetailEntityService::STATUS_PREPARED,
                'continuation' => $saved['id']
            ];
        }

        try {
            $continuationDetailService->createRecords($continuationDetails);
        } catch (\Exception $ex) {
            $continuationService->delete($saved['id']);
            return $this->failedToCreateContinuationDetail();
        }

        return new Response(Response::TYPE_SUCCESS, ['id' => $saved['id']]);
    }

    protected function failedToCreateContinuation()
    {
        $response = new Response(Response::TYPE_FAILED);
        $response->setMessage('Failed to create continuation record, please try again');
        return $response;
    }

    protected function failedToCreateContinuationDetail()
    {
        $response = new Response(Response::TYPE_FAILED);
        $response->setMessage('Failed to create one or more continuation detail records, please try again');
        return $response;
    }
}
