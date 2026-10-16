<?php

namespace Common\Service\Table\Type;

use Common\Service\Table\ContentHelper;
use Common\Util\Escape;

class Action extends AbstractType
{
    private string $format = '<button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit" class="action-button-link %s" name="%s" %s>%s</button>';

    #[\Override]
    public function render(array $data, array $column, string|null $formattedContent = null): string
    {
        $class = $column['class'] ?? '';

        // Each branch has a different provenance, so each is treated differently rather than the
        // whole lot being escaped or left raw.
        if ($formattedContent !== null) {
            // A formatter already rendered this; it may legitimately be markup, and escaping it
            // here would double-escape. Escaping is that formatter's responsibility.
            $value = $formattedContent;
        } elseif (isset($column['text'])) {
            // Developer-authored column config, not row data.
            $value = $column['text'];
        } elseif (isset($column['value_format'])) {
            // A template with row data substituted in — escape the values, keep the template.
            $value = $this->getTable()->replaceContentEscapingValues($column['value_format'], $data);
        } else {
            // A bare row value going straight into the button label — the same case renderBodyColumn
            // handles for a cell, and guarded the same way. A column naming a to-many field holds an
            // array here, which Escape::html() rejects outright; sprintf() below used to turn it
            // into the label "Array", so nothing is lost by labelling the button with nothing.
            $rowValue = isset($column['name']) ? ($data[$column['name']] ?? null) : null;

            $value = ContentHelper::hasStringForm($rowValue) ? Escape::html($rowValue) : '';
        }

        $name = 'action';

        $fieldset = $this->getTable()->getFieldset();
        if (!empty($fieldset)) {
            $name = $fieldset . '[action]';
        }

        // $data['id'] is a row value landing in the quoted name attribute.
        $name .= '[' . $column['action'] . '][' . Escape::html($data['id']) . ']';

        $attributes = $column['action-attributes'] ?? [];

        if (
            $this->isInternalReadOnly()
            && isset($column['keepForReadOnly'])
            && $column['keepForReadOnly'] === true
        ) {
            return $value;
        }

        return sprintf($this->format, $class, $name, implode(' ', $attributes), $value);
    }

    /**
     * Return true if the current internal user has read only permissions
     */
    protected function isInternalReadOnly(): bool
    {
        return $this->table->isInternalReadOnly();
    }
}
