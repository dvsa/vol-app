<?php

/**
 * SaveApplicationChangeOfEntity.php
 */

namespace Olcs\BusinessService\Service\Lva;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\Response;

/**
 * Class SaveApplicationChangeOfEntity
 *
 * Save an applications change of entity.
 *
 * @package Olcs\BusinessService\Service\Lva
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class SaveApplicationChangeOfEntity implements BusinessServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function process(array $params = array())
    {
        $params['details']['licence'] = $this->getServiceLocator()
            ->get('Entity\Application')
            ->getLicenceIdForApplication($params['application']);

        if (!is_null($params['changeOfEntity'])) {
            $params['details']['id'] = $params['changeOfEntity']['id'];
            $params['details']['version'] = $params['changeOfEntity']['version'];
        }

        $changeOfEntityService = $this->getServiceLocator()->get('Entity\ChangeOfEntity');
        $changeOfEntityService->save($params['details']);

        return new Response(Response::TYPE_SUCCESS);
    }
}