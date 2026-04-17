<?php

namespace Olcs\FormService\Form\Lva\OperatingCentre;

use Common\Data\Mapper\Lva\OperatingCentre as OperatingCentreMapper;
use Common\FormService\Form\Lva\OperatingCentre\CommonOperatingCentre;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Validator\ValidateIf;
use Laminas\Form\Form;
use Laminas\Validator\Identical as ValidatorIdentical;

/**
 * Lva Internal Operating Centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LvaOperatingCentre extends CommonOperatingCentre
{
    public function __construct(FormHelperService $formHelper)
    {
        $this->formHelper = $formHelper;
    }

    /**
     * Alter the Form
     *
     * @param Form  $form   Form to alter
     * @param array $params Data
     *
     * @return void
     */
    #[\Override]
    public function alterForm(Form $form, array $params)
    {
        $this->formHelper->removeValidator($form, 'data->permission->permission', ValidatorIdentical::class);
        // On Internal uploading the advert isn't mandatory
        $this->formHelper->removeValidator($form, 'advertisements->uploadedFileCount', ValidateIf::class);

        $appliedVia = null;
        if (isset($params['appliedVia']['id'])) {
            $appliedVia = $params['appliedVia']['id'];
        } elseif (isset($params['appliedVia'])) {
            $appliedVia = $params['appliedVia'];
        }

        $this->formHelper->remove($form, 'advertisements->adPlacedLaterContent');

        $advertisements = $form->get('advertisements');
        $advertisements->setLabel('application_operating-centres_authorisation-sub-action.advertisements.adPlaced');

        $adPlaced = $advertisements->get('radio');

        // Add operator to post option back in just for caseworker form: https://dvsa.atlassian.net/browse/VOL-5814
        $valueOptions = $adPlaced->getValueOptions();
        $valueOptions['adSendByPost'] = 'No (operator to post)';
        $adPlaced->setValueOptions($valueOptions);

        $form->get('data')->get('guidance')->setValue('lva-operating-centre-newspaper-advert');
        $form->get('data')->get('permission')->setLabel('');

        if ($appliedVia === null || $appliedVia !== RefData::APPLIED_VIA_SELFSERVE) {
            $adPlaced = $advertisements->get('radio');
            $valuesOptions = $adPlaced->getValueOptions();
            unset($valuesOptions[OperatingCentreMapper::VALUE_OPTION_AD_UPLOAD_LATER]);
            $adPlaced->setValueOptions($valuesOptions);
        }

        parent::alterForm($form, $params);
    }
}
