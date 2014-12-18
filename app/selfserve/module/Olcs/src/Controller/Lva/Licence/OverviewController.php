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
use Common\Service\Entity\LicenceEntityService;

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

        $variables = ['shouldShowCreateVariation' => true];

        if ($data['licenceType']['id'] === LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            $variables['shouldShowCreateVariation'] = false;
        }

        return new LicenceOverview($data, $this->getAccessibleSections(), $variables);
    }
}
