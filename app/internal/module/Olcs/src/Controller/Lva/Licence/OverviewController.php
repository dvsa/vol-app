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
        $licenceId = $this->getLicenceId();
        $form      = $this->getOverviewForm();
        $service   = $this->getServiceLocator()->get('Entity\Licence');
        $licence   = $service->getExtendedOverview($licenceId);
        $this->alterForm($form, $licence);

        if ($this->getRequest()->isPost()) {
            $data = (array) $this->getRequest()->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if ($this->save($data, $licence)) {
                    $this->addSuccessMessage('Your changes have been saved');
                    return $this->reload();
                }
            }
        } else {
            // Prepare the form with editable data
            $form->setData($this->formatDataForForm($licence));
        }

        $isPsv = $licence['goodsOrPsv']['id'] == LicenceEntityService::LICENCE_CATEGORY_PSV;

        $surrenderedDate = null;
        if ($licence['status']['id'] == LicenceEntityService::LICENCE_STATUS_SURRENDERED) {
            $surrenderedDate = $licence['surrenderedDate'];
        }

        // Collate all the read-only data for the view
        $viewData = [
            'operatorName'              => $licence['organisation']['name'],
            'operatorId'                => $licence['organisation']['id'], // used for URL generation
            'numberOfLicences'          => count($licence['organisation']['licences']),
            'tradingName'               => $this->getTradingName($licence),
            'currentApplications'       => count($licence['applications']),
            'licenceNumber'             => $licence['licNo'],
            'licenceStartDate'          => $licence['inForceDate'],
            'licenceType'               => $service->getShortCodeForType($licence['licenceType']['id']),
            'licenceStatus'             => $licence['status']['id'],
            'surrenderedDate'           => $surrenderedDate,
            'numberOfVehicles'          => $licence['totAuthVehicles'],
            'totalVehicleAuthorisation' => $licence['totAuthVehicles'],
            'numberOfOperatingCentres'  => count($licence['operatingCentres']),
            'totalTrailerAuthorisation' => $isPsv ? null : $licence['totAuthTrailers'], // goods only
            'numberOfIssuedDiscs'       => $isPsv ? count($licence['psvDiscs']) : null, // psv only
            'numberOfCommunityLicences' => $this->getNumberOfCommunityLicences($licence),
            'openCases'                 => $this->getOpenCases($licenceId),
            'currentReviewComplaints'   => $this->getCurrentReviewComplaints($licenceId),
        ];

        // Render the view
        $content = new ViewModel(
            array_merge(
                $viewData,
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

    /**
     * Helper method to get trading name(s) string from licence data
     * (don't really want to clutter the view with this)
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
     * Helper method to get number of community licences from licence data
     * (Standard International and PSV Restricted only, otherwise null)
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

    /**
     * @param Common\Form\Form $form
     * @return Common\Form\Form
     */
    protected function alterForm($form, $licence)
    {
        $form->get('details')->get('leadTcArea')->setValueOptions(
            $this->getServiceLocator()->get('Entity\TrafficArea')->getValueOptions()
        );

        $validStatuses = [
            LicenceEntityService::LICENCE_STATUS_VALID,
            LicenceEntityService::LICENCE_STATUS_SUSPENDED,
            LicenceEntityService::LICENCE_STATUS_CURTAILED,
        ];
        if (in_array($licence['status']['id'], $validStatuses)) {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'details->reviewDate');
        }

        return $form;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function formatDataForForm($data)
    {
        return [
            'details' => [
                'continuationDate' => $data['expiryDate'], // @todo is this correct?
                'reviewDate'       => $data['reviewDate'],
                'id'               => $data['id'],
                'version'          => $data['version'],
                'leadTcArea'       => $data['organisation']['leadTcArea']['id'],
            ]
        ];
    }

    /**
     * @param array $data data to save
     * @param array $licence data (need this to work out organisation id)
     */
    protected function save($data, $licence)
    {
        $dateHelper = $this->getServiceLocator()->get('Helper\Date');

        $licenceSaveData = [];

        $licenceSaveData['expiryDate'] = $dateHelper->getDateObjectFromArray($data['details']['continuationDate'])
            ->format('Y-m-d');

        if (isset($data['details']['reviewDate'])) {
            $licenceSaveData['reviewDate'] = $dateHelper->getDateObjectFromArray($data['details']['reviewDate'])
                ->format('Y-m-d');
        }

        $this->getServiceLocator()->get('Entity\Licence')->forceUpdate($licence['id'], $licenceSaveData);

        $organisationSaveData = [
            'leadTcArea' => $data['details']['leadTcArea']
        ];
        $this->getServiceLocator()->get('Entity\Organisation')->forceUpdate(
            $licence['organisation']['id'],
            $organisationSaveData
        );

        return true;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function formatDataForSave($data)
    {
        return [
            'expiryDate' => $data['details']['continuationDate'],
            'reviewDate' => $data['details']['reviewDate'],
            'id'         => $data['id'],
            'version'    => $data['version'],
            'organisation' => [
                'leadTcArea' => $data['details']['leadTcArea'],
            ],
        ];
    }
}
