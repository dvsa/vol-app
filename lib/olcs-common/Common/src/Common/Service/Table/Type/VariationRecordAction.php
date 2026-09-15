<?php

namespace Common\Service\Table\Type;

class VariationRecordAction extends Action
{
    #[\Override]
    public function render(array $data, array $column, string|null $formattedContent = null): string
    {
        $prefix = null;

        $translator = $this->getTable()->getTranslator();

        if (isset($data['action'])) {
            switch ($data['action']) {
                case 'A':
                    $prefix = 'common.table.status.new';
                    break;
                case 'U':
                    $prefix = 'common.table.status.updated';
                    break;
                case 'C':
                    $prefix = 'common.table.status.current';
                    $column['action-attributes'][] = 'disabled="disabled"';
                    break;
                case 'D':
                    $prefix = 'common.table.status.removed';
                    $column['action-attributes'][] = 'disabled="disabled"';
                    break;
            }
        }

        $prefix = ($prefix !== null ? '(' . $translator->translate($prefix) . ') ' : '');

        $content = parent::render($data, $column, $formattedContent);

        return $prefix . trim($content);
    }
}
