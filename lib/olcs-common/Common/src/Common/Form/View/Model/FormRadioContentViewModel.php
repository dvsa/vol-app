<?php

declare(strict_types=1);

namespace Common\Form\View\Model;

use Laminas\View\Model\ViewModel;

/**
 * @see \CommonTest\Form\View\Model\FormRadioContentViewModelTest
 */
class FormRadioContentViewModel extends ViewModel
{
    /**
     * @inheritDoc
     */
    protected $template = 'partials/form/radio-content';

    public function __construct(array $valueOption)
    {
        parent::__construct([
            'id' => sprintf('%s_content', $valueOption['attributes']['id'] ?? ''),
            'class' => $this->buildClass($valueOption),
            'content' => $valueOption['conditional_content'] ?? '',
        ]);
    }

    protected function buildClass(array $valueOption): string
    {
        $classList = ['govuk-radios__conditional', 'govuk-body'];

        $customClassList = $valueOption['attributes']['class'] ?? [];
        if (! empty($customClassList)) {
            if (is_string($customClassList)) {
                $customClassList = explode(' ', $customClassList);
            }

            $classList = array_unique(array_merge($classList, $customClassList));
        }

        return implode(' ', $classList);
    }
}
