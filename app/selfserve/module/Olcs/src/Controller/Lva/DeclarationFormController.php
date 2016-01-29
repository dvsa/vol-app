<?php

/**
 * Declaration Form Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Application\DeclarationUndertakings;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Declaration Form Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeclarationFormController extends AbstractActionController
{
    public function indexAction()
    {
        $applicationId = $this->params('application');

        $response = $this->handleQuery(DeclarationUndertakings::create(['id' => $applicationId]));

        if (!$response->isOk()) {
            return $this->notFoundAction();
        }

        $applicationData = $response->getResult();

        $params = [
            'isNi' => $applicationData['niFlag'] === 'Y',
            'reference' => $applicationData['licence']['licNo'] . '/' . $applicationData['id'],
            'name' => $applicationData['licence']['organisation']['name'],
            'title' => $this->determineTitle($applicationData),
            'signatureLabel' => $this->determineSignatureLabel($applicationData),
            'undertakings' => $applicationData['undertakings']
        ];

        $view = new ViewModel($params);
        $view->setTemplate('pages/declarations');

        $layout = new ViewModel();
        $layout->setTemplate('layouts/simple');
        $layout->setTerminal(true);
        $layout->addChild($view, 'content');

        return $layout;
    }

    protected function determineTitle($data)
    {
        if ($data['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return 'application-review-title-gv-declaration';
        }

        if ($data['licenceType']['id'] === RefData::LICENCE_TYPE_SPECIAL_RESTRICTED) {
            return 'application-review-title-psv-sr-declaration';
        }

        return 'application-review-title-psv-declaration';
    }

    protected function determineSignatureLabel($data)
    {
        switch ($data['licence']['organisation']['type']['id']) {
            case RefData::ORG_TYPE_SOLE_TRADER:
                return 'declaration-sig-label-st';
            case RefData::ORG_TYPE_OTHER:
                return 'declaration-sig-label-other';
            case RefData::ORG_TYPE_PARTNERSHIP:
                return 'declaration-sig-label-p';
            default:
                return 'declaration-sig-label';
        }
    }
}
