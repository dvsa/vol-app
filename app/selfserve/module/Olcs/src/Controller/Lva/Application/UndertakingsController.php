<?php

/**
 * External Application Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Service\Entity\LicenceEntityService as Licence;

/**
 * External Application Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class UndertakingsController extends Lva\AbstractUndertakingsController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';

    public function indexAction()
    {
        $request = $this->getRequest();

        $applicationId = $this->getApplicationId();
        $applicationData = $this->getServiceLocator()->get('Entity\Application')
            ->getDataForUndertakings($applicationId);

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            // @TODO schema update and get from applicationData
            $applicationData['undertakingsConfirmation'] = true;
            $data['declarationsAndUndertakings']['confirmation'] = $applicationData['undertakingsConfirmation'] ? 'Y' : 'N';
        }

        $data = $this->formatApplicationDataForForm($data, $applicationData);

        $form = $this->getForm()->setData($data);

        if ($request->isPost() && $form->isValid()) {
            $this->postSave('undertakings');
            return $this->completeSection('undertakings');
        }

        return $this->render('undertakings', $form);
    }

    protected function formatApplicationDataForForm($data, $applicationData)
    {
        $licenceType = $applicationData['licenceType']['id'];
        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];

        $data['declarationsAndUndertakings']['undertakings'] = $this->getUndertakingsPartial($goodsOrPsv, $licenceType);
        $data['declarationsAndUndertakings']['declarations'] = $this->getDeclarationsPartial($goodsOrPsv, $licenceType);

        return $data;
    }

    /**
     * Determine correct partial to use for undertakings html
     *
     * (public for unit testing)
     *
     * @param string $goodsOrPsv
     * @param string $licenceType
     * @return string
     */
    public function getUndertakingsPartial($goodsOrPsv, $licenceType)
    {
        $prefix = 'markup-undertakings-';
        $part   = '';

        // valid partials are gv79-standard, gv79-restricted, psv421-standard,
        // psv421-restricted, psv-356
        switch ($goodsOrPsv) {
            case Licence::LICENCE_CATEGORY_PSV:
                switch ($licenceType) {
                    case Licence::LICENCE_TYPE_STANDARD_NATIONAL:
                    case Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL:
                        $part = 'psv421-standard';
                        break;
                    case Licence::LICENCE_TYPE_RESTRICTED:
                        $part = 'psv421-restricted';
                        break;
                    case Licence::LICENCE_TYPE_SPECIAL_RESTRICTED:
                        $part = 'psv356';
                        break;
                    default:
                        break;
                }
                break;
            case Licence::LICENCE_CATEGORY_GOODS_VEHICLE:
                switch ($licenceType) {
                    case Licence::LICENCE_TYPE_STANDARD_NATIONAL:
                    case Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL:
                        $part = 'gv79-standard';
                        break;
                    case Licence::LICENCE_TYPE_RESTRICTED:
                    case Licence::LICENCE_TYPE_SPECIAL_RESTRICTED:
                        $part = 'gv79-restricted';
                        break;
                    default:
                        break;
                }
                break;
        }

        return $prefix.$part;
    }

    /**
     * Determine correct partial to use for declarations html
     *
     * (public for unit testing)
     *
     * @param string $goodsOrPsv
     * @param string $licenceType
     * @return string
     */
    public function getDeclarationsPartial($goodsOrPsv, $licenceType)
    {
        $prefix = 'markup-declarations-';
        $part = '';

        // valid partials are gv79, psv421, psv-356
        switch ($goodsOrPsv) {
            case Licence::LICENCE_CATEGORY_PSV:
                switch ($licenceType) {
                    case Licence::LICENCE_TYPE_STANDARD_NATIONAL:
                    case Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL:
                    case Licence::LICENCE_TYPE_RESTRICTED:
                        $part = 'psv421';
                        break;
                    case Licence::LICENCE_TYPE_SPECIAL_RESTRICTED:
                        $part = 'psv356';
                        break;
                    default:
                        break;
                }
                break;
            case Licence::LICENCE_CATEGORY_GOODS_VEHICLE:
                $part = 'gv79';
                break;
        }

        return $prefix.$part;
    }
}
