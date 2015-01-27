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
        $licence = $this->getServiceLocator()->get('Entity\Licence')
            ->getById($this->getLicenceId());

        $typeOfLicence = $this->getServiceLocator()->get('Entity\Application')
            ->getTypeOfLicenceData($this->getApplicationId());

        $params = [
            'licence' => $licence['licNo'],
            'application' => $this->getIdentifier(),
            'warningText' => $this->getWarningTextTranslationKey(
                $typeOfLicence['goodsOrPsv'],
                $typeOfLicence['licenceType']
            )
        ];

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
