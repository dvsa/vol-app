<?php

/**
 * Process Erru applied penalty response from ATOS
 */
namespace Olcs\BusinessService\Service\Cases\Penalty;

use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\BusinessServiceAwareInterface;
use Common\BusinessService\BusinessServiceAwareTrait;
use Common\BusinessService\Response;
use Common\BusinessRule\BusinessRuleAwareInterface;
use Common\BusinessRule\BusinessRuleAwareTrait;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Common\Service\Cases as CaseService;

/**
 * Send and process Erru applied penalties for a case
 */
class ErruAppliedPenaltyResponse implements
    BusinessServiceInterface,
    BusinessRuleAwareInterface,
    BusinessServiceAwareInterface,
    ServiceLocatorAwareInterface
{
    use BusinessRuleAwareTrait;
    use BusinessServiceAwareTrait;
    use ServiceLocatorAwareTrait;

    /**
     * Processes the data by passing it through a number of business rules and then persisting it
     *
     * @param array $params
     * @return \Common\BusinessService\ResponseInterface
     */
    public function process(array $params)
    {
        $nrService = $this->getServiceLocator()->get('Olcs\Service\Nr\RestHelper');
        $erruResponse = $nrService->sendErruResponse($params['caseId']);

        $response = new Response();

        if ($erruResponse->getStatusCode() == 202) {
            $caseBundle = [
                'children' => [
                    'seriousInfringements' => []
                ]
            ];

            $caseDataService = $this->getServiceLocator()
                ->get('DataServiceManager')
                ->get('Generic\Service\Data\Cases');
            $caseData = $caseDataService->fetchOne($params['caseId'], $caseBundle);

            $caseService = new CaseService();

            //NR will already have checked we have an infringement
            $caseData['seriousInfringements'][0]['erruResponseSent'] = 'Y';
            $caseData['seriousInfringements'][0]['erruResponseUserId'] = $params['user'];
            $caseData['seriousInfringements'][0]['erruResponseTime'] = date('Y-m-d H:i:s');
            $caseData['_OPTIONS_'] = $caseService->getNrCascadeOptions();

            $caseDataService->save($caseData);

            $response->setType(Response::TYPE_SUCCESS);
            $response->setMessage('Response sent successfully');
        } else {
            $response->setType(Response::TYPE_FAILED);
            $response->setMessage('Response could not be sent');
        }

        return $response;
    }
}
