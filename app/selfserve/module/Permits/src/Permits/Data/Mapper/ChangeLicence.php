<?php

namespace Permits\Data\Mapper;

use Common\Exception\BadRequestException;
use Common\Service\Helper\TranslationHelperService;
use Permits\Controller\Config\DataSource\LicencesAvailable as LicencesAvailableDataSource;

/**
 * Change Licence mapper
 */
class ChangeLicence
{
    /** @var TranslationHelperService */
    private $translator;

    /**
     * Create service instance
     *
     * @param TranslationHelperService $translator
     *
     * @return ChangeLicence
     */
    public function __construct(TranslationHelperService $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param array $allData
     *
     * @throws BadRequestException
     */
    public function validateData(array $allData)
    {
        $data = $allData[LicencesAvailableDataSource::DATA_KEY];

        // Get type from application data
        $isEcmt = isset($allData['application']['permitType']);
        $isBilateral = $isEcmt ? false : $allData['application']['irhpPermitType']['name']['id'] === 'permit_annual_bilateral';

        if ($isEcmt && !$data['hasAvailableEcmtLicences'] || $isBilateral && !$data['hasAvailableBilateralLicences']) {
            throw new BadRequestException('No available licences.');
        }

        $selectedLicenceEligible = array_search($allData['licence'], array_column($data['eligibleLicences']['result'], 'id'));

        if ($selectedLicenceEligible === false) {
            throw new BadRequestException('User does not own selected licence.');
        } elseif ($isEcmt && !$data['eligibleLicences']['result'][$selectedLicenceEligible]['canMakeEcmtApplication']) {
            throw new BadRequestException('Selected licence already has an active application.');
        } elseif ($isBilateral && !$data['eligibleLicences']['result'][$selectedLicenceEligible]['canMakeBilateralApplication']) {
            throw new BadRequestException('Selected licence already has an active application.');
        }
    }

    /**
     * @param array $data
     * @param mixed $form
     *
     * @return array
     *
     * @throws BadRequestException
     */
    public function mapForFormOptions(array $data, $form)
    {
        $this->validateData($data);
    
        $mapData = $data[LicencesAvailableDataSource::DATA_KEY];

        $selectedLicenceEligible = array_search(
            $data['licence'],
            array_column($mapData['eligibleLicences']['result'], 'id')
        );

        $confirmChangeLabel = $this->translator->translateReplace(
            'permits.form.change_licence.label',
            [$mapData['eligibleLicences']['result'][$selectedLicenceEligible]['licNo']]
        );

        $form->get('fields')->get('ConfirmChange')->setLabel(
            $confirmChangeLabel
        );

        $form->setData(['fields' => ['licence' => $data['licence']]]);

        return $data;
    }
}
