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

        $form = $this->getOverviewForm();

        $this->alterForm($form);

        $content = new ViewModel(
            array_merge(
                $licenceData,
                ['form' => $form]
            )
        );
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


        $isPsv = $licence['goodsOrPsv']['id'] == LicenceEntityService::LICENCE_CATEGORY_PSV;
        $numberOfIssuedDiscs = null;
        if ($isPsv) {
            // @TODO filter to 'active' or 'pending' (HOW??)
            $numberOfIssuedDiscs = count($licence['psvDiscs']);
        }

        $surrenderedDate = null;
        if ($licence['status']['id'] == LicenceEntityService::LICENCE_STATUS_SURRENDERED) {
            $surrenderedDate = $licence['surrenderedDate'];
        }

        return [
            'operatorName'              => $licence['organisation']['name'],
            'operatorId'                => $licence['organisation']['id'], // used for URL generation
            'numberOfLicences'          => $this->getNumberOfLicences($licence),
            'tradingName'               => $this->getTradingName($licence),
            'currentApplications'       => $this->getCurrentApplications($licence),
            'licenceNumber'             => $licence['licNo'],
            'licenceStartDate'          => $licence['inForceDate'],
            'licenceType'               => $service->getShortCodeForType($licence['licenceType']['id']),
            'licenceStatus'             => $licence['status']['id'],
            'continuationDate'          => '2017-07-31', // move this to bottom, make form control if relevant
            'reviewDate'                => '2018-05-12', // move this to bottom, make form control if relevant
            'surrenderedDate'           => $surrenderedDate,
            'numberOfVehicles'          => $licence['totAuthVehicles'],
            'totalVehicleAuthorisation' => $licence['totAuthVehicles'],
            'numberOfOperatingCentres'  => count($licence['operatingCentres']),
            'totalTrailerAuthorisation' => $isPsv ? null : $licence['totAuthTrailers'], // goods only
            'numberOfIssuedDiscs'       => $isPsv ? $numberOfIssuedDiscs : null, // psv only
            'numberOfCommunityLicences' => $this->getNumberOfCommunityLicences($licence),
            'openCases'                 => $this->getOpenCases($licenceId),
            'currentReviewComplaints'   => $this->getCurrentReviewComplaints($licenceId),
        ];
    }

    /**
     * Get number of licences the operator holds from the licence data
     * @param array $licence
     * @return int
     */
    protected function getNumberOfLicences($licence)
    {
        return count(
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
    }

    /**
     * Get number of applications from licence data
     * @param array $licence
     * @return int
     */
    protected function getCurrentApplications($licence)
    {
        return count(
            array_filter(
                $licence['applications'],
                function ($a) {
                    return in_array(
                        $a['status']['id'],
                        [
                            ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
                            ApplicationEntityService::APPLICATION_STATUS_GRANTED,
                        ]
                    );
                }
            )
        );
    }

    /**
     * Get trading name(s) string from licence data
     * @param array $licence
     * @return string
     */
    protected function getTradingName($licence)
    {
        $tradingNames = array_map(
            function ($t) {
                return $t['name'];
            },
            $licence['organisation']['tradingNames']
        );

        return !empty($tradingNames) ? implode(', ', $tradingNames) : 'None';
    }

    /**
     * Get number of community licences from licence data
     * (Standard Internation and PSV Restricted only)
     *
     * @param array $licence
     * @return int|null
     */
    protected function getNumberOfCommunityLicences($licence)
    {
        $type = $licence['licenceType']['id'];
        $goodsOrPsv = $licence['goodsOrPsv']['id'];

        if ($type == LicenceEntityService::LICENCE_TYPE_STANDARD_INTERNATIONAL
            || ($goodsOrPsv == LicenceEntityService::LICENCE_CATEGORY_PSV
                && $type = LicenceEntityService::LICENCE_TYPE_RESTRICTED)
        ) {
            return (int) $licence['totCommunityLicences'];
        }
    }

    /**
     * @todo how do we work out 'PI' cases?
     * @param int $licenceId
     * @return int
     */
    protected function getOpenCases($licenceId)
    {
        $cases = $this->getServiceLocator()->get('Entity\Cases')
            ->getOpenForLicence($licenceId);

        return count($cases);
    }

    /**
     * @todo how do we work this out?
     * @param int $licenceId
     * @return int
     */
    protected function getCurrentReviewComplaints($licenceId)
    {
        return 99;
    }

    /**
     * @return Common\Form\Form
     */
    protected function getOverviewForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('LicenceOverview');
    }

    protected function alterForm($form)
    {
        $form->get('details')->get('trafficArea')->setValueOptions(
            $this->getServiceLocator()->get('Entity\TrafficArea')->getValueOptions()
        );
    }
}
