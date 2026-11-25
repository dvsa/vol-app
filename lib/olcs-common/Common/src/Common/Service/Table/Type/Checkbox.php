<?php

namespace Common\Service\Table\Type;

use Common\Util\Escape;

class Checkbox extends Selector
{
    protected string $format = '<input type="checkbox" name="%s[]" value="%s" class="govuk-checkboxes__input" %s />';

    #[\Override]
    public function render(array $data, array $column, string|null $formattedContent = null): string
    {
        $input = parent::render($data, $column, $formattedContent);
        $idx = $column['idIndex'] ?? 'id';
        $id = ($this->getTable()->getFieldset() ?? '') . '[id][' . $data[$idx] . ']';

        return '<div class="govuk-checkboxes govuk-checkboxes--small" data-module="govuk-checkboxes">'
            . '<div class="govuk-checkboxes__item">'
            . $input
            . '<label class="govuk-label govuk-checkboxes__label" for="'
            . Escape::htmlAttr($id)
            . '"><span class="govuk-visually-hidden">Select row</span></label>'
            . '</div></div>';
    }
}
