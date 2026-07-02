<?php

namespace Common\Service\Table\Type;

class OperatingCentreVariationRecordAction extends VariationRecordAction
{
    #[\Override]
    public function render(array $data, array $column, string|null $formattedContent = null): string
    {
        $content = parent::render($data, $column, $formattedContent);

        if (isset($data['s4']) && $data['s4'] !== null) {
            $content = str_replace('<button', ' (Schedule 4/1) <button', $content);
        }

        return trim($content);
    }
}
