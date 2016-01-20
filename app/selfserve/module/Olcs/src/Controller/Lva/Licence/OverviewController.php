<?php

/**
 * Licence Overview Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceQry;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Olcs\View\Model\Licence\LicenceOverview;

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
        $data = $this->getOverviewData($this->getLicenceId());
        $data['idIndex'] = $this->getIdentifierIndex();
        $sections = array_keys($data['sections']);

        $variables = ['shouldShowCreateVariation' => true];

        if ($data['licenceType']['id'] === RefData::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            $variables['shouldShowCreateVariation'] = false;
        }

        return new LicenceOverview($data, $sections, $variables);
    }

    /**
     * @NOTE I don't think this is used anymore, I am going to comment it out for a little while and see if anything
     * breaks
     * @todo Remove this code if nothing has broken around creating variations
    public function createVariationAction()
    {
        $varId = $this->getServiceLocator()->get('Entity\Application')
            ->createVariation($this->getIdentifier());

        return $this->redirect()->toRouteAjax('lva-variation', ['application' => $varId]);
    }
     */

    protected function getOverviewData($licenceId)
    {
        $dto = LicenceQry::create(['id' => $licenceId]);
        $response = $this->handleQuery($dto);

        return $response->getResult();
    }
}
