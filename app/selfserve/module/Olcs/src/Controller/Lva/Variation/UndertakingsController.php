<?php

/**
 * External Variation Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Service\Entity\LicenceEntityService as Licence;

/**
 * External Variation Undertakings Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class UndertakingsController extends Lva\AbstractUndertakingsController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
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
            $confirmed = $applicationData['undertakingsConfirmation'] ? 'Y' : 'N';
            $data['declarationsAndUndertakings']['confirmation'] = $confirmed;
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
        // note, for Variations, type of licence comes from nested licence data, unlike application
        $licenceType = $applicationData['licence']['licenceType']['id'];
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

        // valid partials are gv81, gv80a, psv430-431
        switch ($goodsOrPsv) {
            case Licence::LICENCE_CATEGORY_PSV:
                $part = 'psv430-431';
                break;
            case Licence::LICENCE_CATEGORY_GOODS_VEHICLE:
                // @TODO work out if this is an 'upgrade' - gv80a
                switch ($licenceType) {
                    case Licence::LICENCE_TYPE_STANDARD_NATIONAL:
                    case Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL:
                    case Licence::LICENCE_TYPE_RESTRICTED:
                    case Licence::LICENCE_TYPE_SPECIAL_RESTRICTED:
                        $part = 'gv81';
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

        // valid partials are gv81-standard, gv81-restricted, gv80a, psv430-431-standard, psv430-431-restricted
        switch ($goodsOrPsv) {
            case Licence::LICENCE_CATEGORY_PSV:
                switch ($licenceType) {
                    case Licence::LICENCE_TYPE_STANDARD_NATIONAL:
                    case Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL:
                        $part = 'psv430-431-standard';
                        break;
                    case Licence::LICENCE_TYPE_RESTRICTED:
                    case Licence::LICENCE_TYPE_SPECIAL_RESTRICTED:
                        $part = 'psv430-431-restricted';
                        break;
                    default:
                        break;
                }
                break;
            case Licence::LICENCE_CATEGORY_GOODS_VEHICLE:
                // @TODO work out if this is an 'upgrade' - gv80a
                switch ($licenceType) {
                    case Licence::LICENCE_TYPE_STANDARD_NATIONAL:
                    case Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL:
                        $part = 'gv81-standard';
                        break;
                    case Licence::LICENCE_TYPE_RESTRICTED:
                    case Licence::LICENCE_TYPE_SPECIAL_RESTRICTED:
                        $part = 'gv81-restricted';
                        break;
                    default:
                        break;
                }
                break;
        }

        return $prefix.$part;
    }

}
