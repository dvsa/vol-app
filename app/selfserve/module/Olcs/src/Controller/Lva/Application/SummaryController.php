<?php

/**
 * External Application Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Zend\View\Model\ViewModel;
use Common\Service\Entity\LicenceEntityService;

/**
 * External Application Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryController extends AbstractController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';

    public function indexAction()
    {
        $id = $this->getIdentifier();

        $licence = $this->getServiceLocator()->get('Entity\Licence')
            ->getById($this->getLicenceId());

        $typeOfLicence = $this->getServiceLocator()->get('Entity\Application')
            ->getTypeOfLicenceData($id);

        $tms = $this->getServiceLocator()->get('Entity\TransportManagerApplication')
            ->getByApplication($id);

        $params = [
            'licence' => $licence['licNo'],
            'application' => $id,
            'warningText' => $this->getWarningTextTranslationKey(
                $typeOfLicence['goodsOrPsv'],
                $typeOfLicence['licenceType']
            ),
            'actions' => []
        ];

        if (!empty($tms['Results'])) {
            $params['actions'][] = 'summary-application-actions-transport-managers';
        }

        // The conditional for this is out of scope for this story, insert here when in scope
        //if () {
        $params['actions'][] = 'markup-summary-application-actions-document';
        //}

        $view = new ViewModel($params);
        $view->setTemplate('summary-application');

        return $this->render($view);
    }


    /**
     * @todo move this method
     *
     * @param string $goodsOrPsv
     * @param string $licenceType
     * @return string
     */
    protected function getWarningTextTranslationKey($goodsOrPsv, $licenceType)
    {
        if ($this->lva === 'application') {
            if ($goodsOrPsv === LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE) {
                return 'markup-summary-warning-new-goods-application';
            }

            if ($licenceType === LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED) {
                return 'markup-summary-warning-new-psv-sr-application';
            }

            return 'markup-summary-warning-new-psv-application';
        } else {
            if ($goodsOrPsv === LicenceEntityService::LICENCE_CATEGORY_GOODS_VEHICLE) {
                return 'markup-summary-warning-variation-goods-application';
            }

            return 'markup-summary-warning-variation-psv-application';
        }
    }
}
