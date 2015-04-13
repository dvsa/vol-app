<?php

/**
 * Application Goods Vehicles Licence Vehicle Rule
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\BusinessRule\Rule;

use Common\BusinessRule\Rule\ApplicationGoodsVehiclesLicenceVehicle as CommonApplicationGoodsVehiclesLicenceVehicle;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Application Goods Vehicles Licence Vehicle Rule
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationGoodsVehiclesLicenceVehicle extends CommonApplicationGoodsVehiclesLicenceVehicle implements
    ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function validate($data, $mode, $vehicleId, $licenceId, $applicationId)
    {
        $data = parent::validate($data, $mode, $vehicleId, $licenceId, $applicationId);

        if ($mode === 'add') {
            $data['specifiedDate'] = $this->getServiceLocator()->get('Helper\Date')->getDate();
        }

        return $data;
    }
}
