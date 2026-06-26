<?php

namespace Common\FormService\Form\Lva;

use Common\Form\Elements\Types\HtmlTranslated;
use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;
use Laminas\Http\Request;

/**
 * FinancialHistory Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialHistory
{
    public function __construct(protected FormHelperService $formHelper, protected TranslationHelperService $translator)
    {
    }

    /**
     * Get form
     *
     * @param Request $request Request
     * @param array   $data    Data for form
     *
     * @return Form
     */
    public function getForm($request, array $data = [])
    {
        /** @var Form $form */
        $form = $this->formHelper->createFormWithRequest('Lva\FinancialHistory', $request);

        $this->alterForm($form, $data);

        return $form;
    }

    /**
     * Make form alterations
     *
     * @param Form  $form Form
     * @param array $data Data for form
     *
     * @return Form
     */
    protected function alterForm(Form $form, array $data = [])
    {
        if (isset($data['lva']) && in_array($data['lva'], ['variation', 'application'])) {
            $this->updateInsolvencyConfirmationLabel($form, $data);
        }

        if (isset($data['variationType']) && $data['variationType'] == RefData::VARIATION_TYPE_DIRECTOR_CHANGE) {
            $this->formHelper->remove($form, 'data->financeHint');
            $this->formHelper->remove($form, 'data->financialHistoryConfirmation');

            /** @var FieldsetInterface $dataFieldset */
            $dataFieldset = $form->get('data');

            /** @var HtmlTranslated $hasAnyPerson */
            $hasAnyPerson = $dataFieldset->get('hasAnyPerson');

            $hasAnyPersonToken = $this->translator->translate($this->getCorrectHasAnyPersonKey($data['organisationType']));
            $hasAnyPerson->setTokens([$hasAnyPersonToken]);
        }

        return $form;
    }

    /**
     * If the licence is NI then update the label.  Used in current controller
     * and CommonVariationControllerTrait.
     *
     * @param Form  $form Form
     * @param array $data Api/Form Data
     *
     * @return Form
     */
    protected function updateInsolvencyConfirmationLabel(Form $form, $data = null)
    {
        if (isset($data['niFlag']) && $data['niFlag'] === 'Y') {
            /** @var FieldsetInterface $dataFieldset */
            $dataFieldset = $form->get('data');
            /** @var FieldsetInterface $financialHistoryConfirmationFieldset */
            $financialHistoryConfirmationFieldset = $dataFieldset->get('financialHistoryConfirmation');
            /** @var ElementInterface $insolvencyConfirmationField */
            $insolvencyConfirmationField = $financialHistoryConfirmationFieldset
                ->get('insolvencyConfirmation');
            $insolvencyConfirmationField->setLabel(
                'application_previous-history_financial-history.insolvencyConfirmation.title.ni'
            );
        }

        return $form;
    }

    /**
     * Get the correct has any person question for organisation type
     *
     * @param string $organisationType the organisation type
     *
     * @return string
     */
    private function getCorrectHasAnyPersonKey($organisationType)
    {
        return match ($organisationType) {
            RefData::ORG_TYPE_REGISTERED_COMPANY => 'selfserve-app-subSection-person-financial-history-has-director',
            RefData::ORG_TYPE_LLP, RefData::ORG_TYPE_PARTNERSHIP => 'selfserve-app-subSection-person-financial-history-has-partner',
            default => 'selfserve-app-subSection-person-financial-history-has-person',
        };
    }
}
