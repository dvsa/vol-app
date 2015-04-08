<?php

/**
 * Licence Overview
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
 * Licence Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class LicenceOverview implements
    BusinessServiceInterface,
    ServiceLocatorAwareInterface,
    BusinessRuleAwareInterface
{
    use ServiceLocatorAwareTrait,
        BusinessRuleAwareTrait;

    /**
     * Formats licence data from the overview form and persists changes
     *
     * @param array $params
     * @return Common\BusinessService\ResponseInterface
     */
    public function process(array $params)
    {
        $licenceSaveData = [];
        $saved = false;

        $checkDate = $this->getBusinessRuleManager()->get('CheckDate');

        if (isset($params['details']['continuationDate'])) {
            $licenceSaveData['expiryDate'] = $checkDate->validate($params['details']['continuationDate']);
            if ($licenceSaveData['expiryDate'] === null) {
                unset($licenceSaveData['expiryDate']);
            }
        }

        if (isset($params['details']['reviewDate'])) {
            $licenceSaveData['reviewDate'] = $checkDate->validate($params['details']['reviewDate']);
            if ($licenceSaveData['reviewDate'] === null) {
                unset($licenceSaveData['reviewDate']);
            }
        }

        if (!empty($licenceSaveData)) {
            $licenceSaveData['id'] = $params['id'];
            $licenceSaveData['version'] = $params['version'];
            $this->getServiceLocator()->get('Entity\Licence')->save($licenceSaveData);
            $saved = true;
        }

        if (isset($data['details']['leadTcArea'])) {
            $organisation = $this->getServiceLocator()->get('Entity\Licence')->getOrganisation($params['id']);
            $organisationSaveData = [
                'leadTcArea' => $data['details']['leadTcArea']
            ];
            $this->getServiceLocator()->get('Entity\Organisation')->forceUpdate(
                $organisation['id'],
                $organisationSaveData
            );
            $saved = true;
        }

        $responseType = $saved ? Response::TYPE_SUCCESS : Response::TYPE_NO_OP;

        return new Response($responseType);
    }
}
