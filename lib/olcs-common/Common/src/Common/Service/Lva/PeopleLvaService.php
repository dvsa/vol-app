<?php

/**
 * People LVA service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Common\Service\Lva;

use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\FieldsetInterface;
use Laminas\Form\Form;

/**
 * People LVA service
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class PeopleLvaService
{
    public function __construct(private FormHelperService $formHelper)
    {
    }

    /**
     * lock person form
     *
     * @param Form  $form    form
     * @param mixed $orgType organisation type
     */
    public function lockPersonForm(Form $form, mixed $orgType): void
    {
        /** @var FieldsetInterface $fieldset */
        $fieldset = $form->get('data');

        foreach (['title', 'forename', 'familyName', 'otherName', 'birthDate', 'position'] as $field) {
            if ($fieldset->has($field)) {
                $this->formHelper->lockElement(
                    $fieldset->get($field),
                    'people.' . $orgType . '.' . $field . '.locked'
                );
                $this->formHelper->disableElement($form, 'data->' . $field);
            }
        }

        if ($orgType !== RefData::ORG_TYPE_SOLE_TRADER) {
            $this->formHelper->remove($form, 'form-actions->submit');
        }

        $form->setAttribute('locked', true);
    }

    /**
     * lock the partnership form
     *
     * @param Form  $form  form
     * @param mixed $table table
     */
    public function lockPartnershipForm(Form $form, mixed $table): void
    {
        $table->removeActions();
        $table->removeColumn('select');
    }

    /**
     * lock the organisation form
     *
     * @param Form  $form  form
     * @param mixed $table table
     */
    public function lockOrganisationForm(Form $form, mixed $table): void
    {
        $table->removeActions();
        $table->removeColumn('select');
        $table->removeColumn('actionLinks');
    }
}
