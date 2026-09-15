<?php

namespace Common\Form\Elements\InputFilters;

use Laminas\Form\Element\Textarea as LaminasElement;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator;

/**
 * Input Specification for Finacial History additional info
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class FinancialHistoryTextarea extends LaminasElement implements InputProviderInterface
{
    /**
     * Provide default input rules for this element.
     *
     * @return ((Validator\NotEmpty|\Dvsa\Olcs\Transfer\Validators\FhAdditionalInfo)[]|null|string|true)[]
     *
     * @psalm-return array{name: null|string, required: true, validators: list{Validator\NotEmpty, \Dvsa\Olcs\Transfer\Validators\FhAdditionalInfo}}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => true,
            'validators' => [
                new Validator\NotEmpty(Validator\NotEmpty::NULL),
                new \Dvsa\Olcs\Transfer\Validators\FhAdditionalInfo(),
            ]
        ];
    }
}
