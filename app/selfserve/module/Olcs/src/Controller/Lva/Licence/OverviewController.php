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

    protected function getOverviewData($licenceId)
    {
        $dto = LicenceQry::create(['id' => $licenceId]);
        $response = $this->handleQuery($dto);

        return $response->getResult();
    }
}
