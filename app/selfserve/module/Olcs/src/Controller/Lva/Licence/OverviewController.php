<?php

/**
 * Licence Overview Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Olcs\View\Model\Licence\LicenceOverview;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Controller\Lva\AbstractController;

/**
 * Licence Overview Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';

    /**
     * Licence overview
     */
    public function indexAction()
    {
        $data = $this->getServiceLocator()->get('Entity\Licence')->getOverview($this->getLicenceId());
        $data['idIndex'] = $this->getIdentifierIndex();

        return new LicenceOverview($data, $this->getAccessibleSections());
    }
}
