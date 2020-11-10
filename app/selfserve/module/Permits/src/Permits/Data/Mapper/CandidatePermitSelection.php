<?php

namespace Permits\Data\Mapper;

use Common\Form\Elements\Custom\EcmtCandidatePermitSelectionValidatingElement;
use Common\Form\Elements\InputFilters\SingleCheckbox;
use Common\Form\Form;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Table\TableFactory;

/**
 * Candidate permit selection mapper
 */
class CandidatePermitSelection
{
    /** @var HtmlAdder */
    private $htmlAdder;

    /** @var TableFactory */
    private $tableFactory;

    /**
     * Create service instance
     *
     * @param HtmlAdder $htmlAdder
     * @param TableFactory $tableFactory
     *
     * @return CandidatePermitSelection
     */
    public function __construct(HtmlAdder $htmlAdder, TableFactory $tableFactory)
    {
        $this->htmlAdder = $htmlAdder;
        $this->tableFactory = $tableFactory;
    }

    /**
     * @param array $data
     * @param Form $form
     *
     * @return array
     */
    public function mapForFormOptions(array $data, $form)
    {
        $fieldset = $form->get('fields');

        $fieldset->add(
            [
                'type' => EcmtCandidatePermitSelectionValidatingElement::class,
                'name' => 'candidatePermitSelectionValidator'
            ]
        );

        $table = $this->tableFactory->prepareTable('candidate-permits-selection', $data['unpaginatedUnpaidPermits']);

        $tableMarkup = $table->__toString();

        $tableMarkupElements = explode('{checkboxPlaceholder}', $tableMarkup);
        $tableMarkupElementsCount = count($tableMarkupElements);
        $candidatePermits = $data['unpaginatedUnpaidPermits']['results'];

        for ($index = 1; $index <= $tableMarkupElementsCount; $index++) {
            $tableMarkupElement = $tableMarkupElements[$index-1];
            $this->htmlAdder->add($fieldset, 'table' . $index, $tableMarkupElement);

            if ($index < $tableMarkupElementsCount) {
                $candidatePermit = $candidatePermits[$index-1];
                $checkboxId = 'candidate-' . $candidatePermit['id'];

                $fieldset->add(
                    [
                        'type' => SingleCheckbox::class,
                        'name' => $checkboxId,
                        'attributes' => [
                            'class' => 'govuk-checkboxes__input',
                            'id' => $checkboxId,
                            'value' => ($candidatePermit['wanted'] === true) ? '1' : '0',
                            'data-container-class' => 'govuk-checkboxes__item',
                        ],
                        'options' => [
                            'label' => '<span class="govuk-visually-hidden">Select permit ' . $index . '</span>',
                            'label_attributes' => [
                                'class' => 'form-control form-control--checkbox form-control--advanced'
                            ],
                            'label_options' => [
                                'disable_html_escape' => true
                            ],
                            'checked_value' => '1',
                            'unchecked_value' => '0',
                        ]
                    ]
                );
            }
        }

        return $data;
    }
}
