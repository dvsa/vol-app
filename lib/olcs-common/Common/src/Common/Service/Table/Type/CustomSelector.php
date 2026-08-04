<?php

namespace Common\Service\Table\Type;

class CustomSelector extends Selector
{
    protected string $format = '<input type="radio" name="%s" value="%s" %s />';

    /**
     * Render custom selector. Allows overriding the name field and the data used to generate the value
     * To override name Use existing $column['name'] default is 'id'
     * To override value element use new $column['data-field'] default is 'id'
     */
    #[\Override]
    public function render(array $data, array $column, string|null $formattedContent = null): string
    {
        $fieldset = $this->getTable()->getFieldset();

        $name = 'id';

        if (isset($column['name'])) {
            $name = $column['name'];
        }

        if (!empty($fieldset)) {
            $name = $fieldset . '[' . $name . ']';
        }

        $dataField = 'id';

        if (isset($column['data-field'])) {
            $dataField = $column['data-field'];
        }

        [$attributes, $column, $data] = $this->transformDataAttributes($column, $data);

        if (isset($column['disableIfRowIsDisabled']) && $this->getTable()->isRowDisabled($data)) {
            $attributes[] = 'disabled="disabled"';
        }

        return sprintf($this->format, $name, $data[$dataField], implode(' ', $attributes));
    }
}
