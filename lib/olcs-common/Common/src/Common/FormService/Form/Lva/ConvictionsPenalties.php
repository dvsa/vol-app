<?php

namespace Common\FormService\Form\Lva;

use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Dvsa\Olcs\Api\Entity\Licence;

/**
 * Convictions and Penalties Form
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ConvictionsPenalties
{
    public function __construct(protected FormHelperService $formHelper, protected TranslationHelperService $translator, protected UrlHelperService $urlHelper)
    {
    }

    /**
     * get the form
     *
     * @param array $params parameters used for switching/configuring form
     *
     * @return \Common\Form\Form|\Laminas\Form\Form
     */
    public function getForm(array $params = [])
    {
        $form = $this->formHelper->createForm('Lva\ConvictionsPenalties');
        $this->alterForm($form, $params);
        return $form;
    }

    /**
     * Determine if form changes required as is director variation
     *
     * @param array $params parameters used for switching/configuring form
     *
     * @return bool
     */
    public function isDirectorChange(array $params)
    {
        return isset($params['variationType']) &&
            $params['variationType'] === RefData::VARIATION_TYPE_DIRECTOR_CHANGE;
    }

    /**
     * Make form alterations
     *
     * @param \Laminas\Form\Form $form   form
     * @param array           $params parameters used for switching/configuring form
     *
     * @return \Laminas\Form\Form form
     *
     */
    protected function alterForm($form, array $params)
    {
        $this->changeFormForDirectorVariation($form, $params);
        $getConvictionsReadMoreLink = $form->get('convictionsReadMoreLink')->get('readMoreLink');
        $routeParam = $this->translator->translate('convictions-and-penalties-guidance-route-param');
        $getConvictionsReadMoreRoute = $this->urlHelper->fromRoute(
            'guides/guide',
            ['guide' => $routeParam]
        );
        $getConvictionsReadMoreLink->setValue($getConvictionsReadMoreRoute);

        return $form;
    }

    /**
     * Alter the form standard buttons
     *
     * @param \Laminas\Form\Form $form form
     *
     *
     */
    private function alterFormButtons($form): void
    {
        $formActions = $form->get('form-actions');
        $formActions->get('saveAndContinue')->setLabel('Submit details');
    }

    /**
     * Alter the confirmation message
     *
     * @param \Laminas\Form\Form $form form
     */
    private function removeConfirmation($form): void
    {
        $form->remove('convictionsConfirmation');
    }

    /**
     * Set dynamic label based on organisation type
     *
     * @param \Laminas\Form\Fieldset $dataTable fieldset for table
     * @param array               $params    parameters
     */
    private function alterFormHeading($dataTable, array $params): void
    {
        $label = $dataTable->getLabel();
        $dataTable->setLabel($label . '-' . $params['organisationType'] . "-dc");
        $existingClasses = $dataTable->getAttribute('class');
        $dataTable->setAttribute('class', $existingClasses . ' five-eights');
    }

    /**
     * remove form question text
     *
     * @param \Laminas\Form\Fieldset $dataTable data table
     */
    private function alterFormQuestion($dataTable): void
    {
        $question = $dataTable->get('question');
        $question->setLabel('');
    }

    /**
     * Change the form for director variation
     *
     * @param \Laminas\Form\Form $form   form
     * @param array           $params params
     */
    public function changeFormForDirectorVariation($form, array $params): void
    {
        if ($this->isDirectorChange($params)) {
            $dataTable = $form->get('data');
            $existingClasses = $dataTable->getAttribute('class');
            $dataTable->setAttribute('class', $existingClasses . ' director-change');
            $this->alterFormQuestion($dataTable);
            $this->alterFormHeading($dataTable, $params);
            $this->alterFormButtons($form);
            $this->removeConfirmation($form);
            $this->formHelper->remove($form, 'form-actions->save');
        }
    }
}
