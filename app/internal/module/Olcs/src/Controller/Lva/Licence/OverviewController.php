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
        $licenceId  = $this->getLicenceId();
        $form       = $this->getOverviewForm();
        $service    = $this->getServiceLocator()->get('Entity\Licence');
        $licence    = $service->getExtendedOverview($licenceId);
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $this->alterForm($form, $licence);

        if ($this->getRequest()->isPost()) {
            $data = (array) $this->getRequest()->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                $this->save($data, $licence);
                $this->addSuccessMessage('Your changes have been saved');
                return $this->reload();
            }
        } else {
            // Prepare the form with editable data
            $form->setData($this->formatDataForForm($licence));
        }

        $isPsv = $licence['goodsOrPsv']['id'] == LicenceEntityService::LICENCE_CATEGORY_PSV;

        $isSpecialRestricted = $licence['licenceType']['id'] == LicenceEntityService::LICENCE_TYPE_SPECIAL_RESTRICTED;

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
            'currentApplications'       => $this->getCurrentApplications($licence),
            'licenceNumber'             => $licence['licNo'],
            'licenceStartDate'          => $licence['inForceDate'],
            'licenceType'               => $service->getShortCodeForType($licence['licenceType']['id']),
            'licenceStatus'             => $translator->translate($licence['status']['id']),
            'surrenderedDate'           => $surrenderedDate,
            'numberOfVehicles'          => $isSpecialRestricted ? null : count($licence['licenceVehicles']),
            'totalVehicleAuthorisation' => $isSpecialRestricted ? null : $licence['totAuthVehicles'],
            'numberOfOperatingCentres'  => $isSpecialRestricted ? null : count($licence['operatingCentres']),
            'totalTrailerAuthorisation' => $isPsv ? null : $licence['totAuthTrailers'],
            'numberOfIssuedDiscs'       => $isPsv && !$isSpecialRestricted ? count($licence['psvDiscs']) : null,
            'numberOfCommunityLicences' => $this->getNumberOfCommunityLicences($licence),
            'openCases'                 => $this->getOpenCases($licenceId),

            // out of scope for OLCS-5209
            'currentReviewComplaints'    => null,
            'originalOperatorName'       => null,
            'originalLicenceNumber'      => null,
            'receivesMailElectronically' => null,
            'registeredForSelfService'   => null,
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
     * Helper method to get the first trading name from licence data.
     * (Sorts trading names by createdOn date then alphabetically)
     *
     * @param array $licence
     * @return string
     */
    protected function getTradingName($licence)
    {
        if (empty($licence['organisation']['tradingNames'])) {
            return 'None';
        }

        usort(
            $licence['organisation']['tradingNames'],
            function ($a, $b) {
                if ($a['createdOn'] == $b['createdOn']) {
                    // This *should* be an extreme edge case but there is a bug
                    // in Business Details causing trading names to have the
                    // same createdOn date. Sort alphabetically to avoid
                    // 'random' behaviour.
                    return strcasecmp($a['name'], $b['name']);
                }
                return strtotime($a['createdOn']) < strtotime($b['createdOn']) ? -1 : 1;
            }
        );

        return array_shift($licence['organisation']['tradingNames'])['name'];
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
                && $type == LicenceEntityService::LICENCE_TYPE_RESTRICTED)
        ) {
            return (int) $licence['totCommunityLicences'];
        }

        return null;
    }

    /**
     * @param int $licenceId
     * @return string (count may be suffixed with '(PI)')
     */
    protected function getOpenCases($licenceId)
    {
        $cases = $this->getServiceLocator()->get('Entity\Cases')
            ->getOpenForLicence($licenceId);

        $openCases = (string) count($cases);

        foreach ($cases as $c) {
            if (!empty($c['publicInquirys'])) {
                $openCases .= ' (PI)';
                break;
            }
        }

        return $openCases;
    }

    /**
     * Helper method to get number of current applications for the organisation
     * from licence data
     *
     * @param array $licence
     * @return int
     */
    protected function getCurrentApplications($licence)
    {
        $applications = $this->getServiceLocator()->get('Entity\Organisation')->getAllApplicationsByStatus(
            $licence['organisation']['id'],
            [
                ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION,
                ApplicationEntityService::APPLICATION_STATUS_GRANTED,
            ]
        );

        return count($applications);
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
        if (!in_array($licence['status']['id'], $validStatuses)) {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'details->reviewDate');
        }

        if (count($licence['organisation']['licences']) <= 1) {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'details->leadTcArea');
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
                'continuationDate' => $data['expiryDate'],
                'reviewDate'       => $data['reviewDate'],
                'leadTcArea'       => $data['organisation']['leadTcArea']['id'],
            ],
            'id' => $data['id'],
            'version' => $data['version'],
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

        if (isset($data['details']['leadTcArea'])) {
            $organisationSaveData = [
                'leadTcArea' => $data['details']['leadTcArea']
            ];
            $this->getServiceLocator()->get('Entity\Organisation')->forceUpdate(
                $licence['organisation']['id'],
                $organisationSaveData
            );
        }
    }
}
