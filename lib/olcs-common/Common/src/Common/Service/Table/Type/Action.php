<?php

namespace Common\Service\Table\Type;

class Action extends AbstractType
{
    private string $format = '<button data-prevent-double-click="true" data-module="govuk-button" role="link" type="submit" class="action-button-link %s" name="%s" %s>%s</button>';

    #[\Override]
    public function render(array $data, array $column, string|null $formattedContent = null): string
    {
        $class = $column['class'] ?? '';

        if ($formattedContent !== null) {
            $value = $formattedContent;
        } elseif (isset($column['text'])) {
            $value = $column['text'];
        } elseif (isset($column['value_format'])) {
            $value = $this->getTable()->replaceContent($column['value_format'], $data);
        } else {
            $value = (isset($column['name']) && isset($data[$column['name']]) ? $data[$column['name']] : '');
        }

        $name = 'action';

        $fieldset = $this->getTable()->getFieldset();
        if (!empty($fieldset)) {
            $name = $fieldset . '[action]';
        }

        $name .= '[' . $column['action'] . '][' . $data['id'] . ']';

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
