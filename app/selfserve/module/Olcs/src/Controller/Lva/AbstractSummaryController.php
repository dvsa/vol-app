<?php

/**
 * External Abstract Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Zend\View\Model\ViewModel;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Application\TransportManagers as Qry;

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
        $data = $this->getData();
        $view = new ViewModel($this->buildSummaryParams($data));
        $view->setTemplate('pages/application-summary');

        return $this->render($view);
    }

    public function postSubmitSummaryAction()
    {
        $application = $this->getData();
        $params = $this->buildSummaryParams($application);

        $params['lva'] = $this->lva;
        $params['status'] = $application['status']['description'];
        $params['submittedDate'] = date('d F Y', strtotime($application['receivedDate']));
        $params['targetCompletionDate'] = date('d F Y', strtotime($application['targetCompletionDate']));
        $params['interimStatus'] = $application['interimStatus']
            ? $application['interimStatus']['description']
            : null;
        $params['interimStartDate'] = $application['interimStart'];

        $view = new ViewModel($params);
        $view->setTemplate('pages/application-post-submit-summary');

        return $this->render($view);
    }

    protected function getData()
    {
        $id = $this->getIdentifier();

        $dto = Qry::create(['id' => $id]);
        $response = $this->handleQuery($dto);

        if ($response->isOk()) {
            return $response->getResult();
        }
    }

    protected function buildSummaryParams($data)
    {
        $params = [
            'licence' => $data['licence']['licNo'],
            'application' => $data['id'],
            'warningText' => $this->getWarningTextTranslationKey(
                $data['goodsOrPsv']['id'],
                $data['licenceType']['id']
            ),
            'actions' => [],
            'canWithdraw' => ($data['status']['id'] === RefData::APPLICATION_STATUS_UNDER_CONSIDERATION)
        ];

        if (!empty($data['transportManagers'])) {
            $params['actions'][] = 'summary-application-actions-transport-managers';
        }

        // The conditional for this is out of scope for this story, insert here when in scope
        //if () {
        $params['actions'][] = 'markup-summary-application-actions-document';
        //}

        // get payment reference from route, if any
        $params['paymentRef'] = $this->params()->fromRoute('reference');

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
            if ($goodsOrPsv === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
                return 'markup-summary-warning-new-goods-application';
            }

            if ($licenceType === RefData::LICENCE_TYPE_SPECIAL_RESTRICTED) {
                return 'markup-summary-warning-new-psv-sr-application';
            }

            return 'markup-summary-warning-new-psv-application';
        } else {
            if ($goodsOrPsv === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
                return 'markup-summary-warning-variation-goods-application';
            }

            return 'markup-summary-warning-variation-psv-application';
        }
    }
}
