<?php

/**
 * Internal Licence Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\Licence\AbstractTypeOfLicenceController;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits;

/**
 * Internal Licence Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractTypeOfLicenceController implements LicenceControllerInterface
{
    use Traits\LicenceControllerTrait;

    protected $location = 'internal';
    protected $lva = 'licence';

    /**
     * indexAction
     *
     * @return \Zend\Http\Response
     */
    public function indexAction()
    {
        $this->getServiceLocator()->get('Helper\Guidance')->append('licence_type_of_licence_change');

        return parent::indexAction();
    }
}
