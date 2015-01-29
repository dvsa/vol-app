<?php

/**
 * External Abstract Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Zend\View\Model\ViewModel;
use Common\Service\Entity\LicenceEntityService;

/**
 * External Abstract Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractSummaryController extends AbstractController
{
    protected $location = 'external';

    public function indexAction()
    {
        $view = new ViewModel($this->buildSummaryParams());
        $view->setTemplate('pages/application-summary');

        return $this->render($view);
    }

    public function postSubmitSummaryAction()
    {
        $params = $this->buildSummaryParams();

        $application = $this->getServiceLocator()->get('Entity\Application')
            ->getSubmitSummaryData($this->getIdentifier());

        $params['status'] = $application['status']['description'];
        $params['submittedDate'] = date('d F Y', strtotime($application['receivedDate']));
        $params['targetCompletionDate'] = date('d F Y', strtotime($application['targetCompletionDate']));

        $view = new ViewModel($params);
        $view->setTemplate('pages/application-post-submit-summary');

        return $this->render($view);
    }

    protected function buildSummaryParams()
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

        return $params;
    }

    /**
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
