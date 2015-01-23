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

    protected function formatDataForForm($applicationData)
    {
        $licenceType = $applicationData['licenceType']['id'];
        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];
        $niFlag      = $applicationData['niFlag'];

        $formData = [
            'declarationConfirmation' => $applicationData['declarationConfirmation'],
            'version' => $applicationData['version'],
            'id' => $applicationData['id'],
            'undertakings' => $this->getUndertakingsPartial($goodsOrPsv, $licenceType, $niFlag),
            'declarations' => $this->getDeclarationsPartial($goodsOrPsv, $licenceType, $niFlag),
        ];

        return ['declarationsAndUndertakings' => $formData];
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
    public function getUndertakingsPartial($goodsOrPsv, $licenceType, $niFlag)
    {
        $prefix = 'markup-undertakings-';
        $part   = '';

        // valid partials are gv79-standard, gv79-restricted,
        // gvni79-standard, gvni79-restricted,
        // psv421-standard, psv421-restricted, psv-356
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
                        throw new \LogicException('Licence Type not set or invalid');
                        break;
                }
                break;
            case Licence::LICENCE_CATEGORY_GOODS_VEHICLE:
                switch ($licenceType) {
                    case Licence::LICENCE_TYPE_STANDARD_NATIONAL:
                    case Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL:
                        if ($niFlag == 'Y') {
                            $part = 'gvni79-standard';
                        } else {
                            $part = 'gv79-standard';
                        }
                        break;
                    case Licence::LICENCE_TYPE_RESTRICTED:
                        if ($niFlag == 'Y') {
                            $part = 'gvni79-restricted';
                        } else {
                            $part = 'gv79-restricted';
                        }
                        break;
                    default:
                        throw new \LogicException('Licence Type not set or invalid');
                        break;
                }
                break;
            default:
                throw new \LogicException('Licence Category not set or invalid');
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
    public function getDeclarationsPartial($goodsOrPsv, $licenceType, $niFlag)
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
                        throw new \LogicException('Licence Type not set or invalid');
                        break;
                }
                break;
            case Licence::LICENCE_CATEGORY_GOODS_VEHICLE:
                switch ($licenceType) {
                    case Licence::LICENCE_TYPE_STANDARD_NATIONAL:
                    case Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL:
                    case Licence::LICENCE_TYPE_RESTRICTED:
                        $part = 'gv79';
                        break;
                    default:
                        throw new \LogicException('Licence Type not set or invalid');
                        break;
                }
                break;
            default:
                throw new \LogicException('Licence Category not set or invalid');
                break;
        }

        return $prefix.$part;
    }
}
