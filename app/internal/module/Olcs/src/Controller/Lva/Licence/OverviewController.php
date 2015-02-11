<?php

/**
 * Internal Licence Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Zend\View\Model\ViewModel;
use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Entity\ApplicationEntityService;

/**
 * Internal Licence Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractController implements
    LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';

    /**
     * Licence overview
     */
    public function indexAction()
    {
        $licenceData = $this->getViewDataForLicence($this->getLicenceId());

        $content = new ViewModel($licenceData);
        $content->setTemplate('pages/licence/overview');

        return $this->render($content);
    }

    public function createVariationAction()
    {
        $varId = $this->getServiceLocator()->get('Entity\Application')
            ->createVariation($this->getIdentifier());

        return $this->redirect()->toRouteAjax('lva-variation', ['application' => $varId]);
    }

    protected function getViewDataForLicence($licenceId)
    {
        $service = $this->getServiceLocator()->get('Entity\Licence');

        $licence = $service->getExtendedOverview($licenceId);

        $tradingNames = array_map(
            function ($t) {
                return $t['name'];
            },
            $licence['organisation']['tradingNames']
        );

        $licenceType = $service->getShortCodeForType($licence['licenceType']['id']);

        $numberOfLicences = count(
            array_filter(
                $licence['organisation']['licences'],
                function ($l) {
                    return in_array(
                        $l['status']['id'],
                        [
                            LicenceEntityService::LICENCE_STATUS_VALID,
                            LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                            LicenceEntityService::LICENCE_STATUS_CURTAILED,
                        ]
                    );
                }
            )
        );

        $currentApplications = count(
            array_filter(
                $licence['applications'],
                function ($a) {
                    return in_array(
                        $a['status']['id'],
                        [
                            ApplicationEntityService::STATUS_UNDER_CONSIDERATION,
                            ApplicationEntityService::STATUS_GRANTED,
                        ]
                    );
                }
            )
        );

        $isPsv = $licence['goodsOrPsv']['id'] == LicenceEntityService::LICENCE_CATEGORY_PSV;

        $numberOfIssuedDiscs = null;
        if ($isPsv) {
            // @TODO filter to 'active' or 'pending' (HOW??)
            $numberOfIssuedDiscs = count($licence['psvDiscs']);
        }

        return [
            'operatorName'              => $licence['organisation']['name'],
            'operatorId'                => $licence['organisation']['id'], // for URL
            'numberOfLicences'          => $numberOfLicences,
            'tradingName'               => !empty($tradingNames) ? implode(', ', $tradingNames) : 'None',
            'currentApplications'       => $currentApplications,
            'licenceNumber'             => $licence['licNo'],
            'licenceStartDate'          => $licence['inForceDate'],
            'licenceType'               => $licenceType,
            'licenceStatus'             => $licence['status']['id'],
            'continuationDate'          => '2017-07-31', // move this to bottom, make form control if relevant
            'reviewDate'                => '2018-05-12', // move this to bottom, make form control if relevant
            'surrenderedDate'           => '2015-01-10', //only show if relevant
            'numberOfVehicles'          => $licence['totAuthVehicles'],
            'totalVehicleAuthorisation' => $licence['totAuthVehicles'],
            'numberOfOperatingCentres'  => count($licence['operatingCentres']),
            'totalTrailerAuthorisation' => $isPsv ? null : $licence['totAuthTrailers'], // goods only
            'numberOfIssuedDiscs'       => $isPsv ? $numberOfIssuedDiscs : null, // psv only
            'numberOfCommunityLicences' => 'XXX', // SI and PSV/R only
            'openCases'                 => 'XXX',
            'currentReviewComplaints'   => 'XXX',
        ];
    }
}
