<?php

namespace Common\Service\Table\Type;

class OperatingCentreAction extends Action
{
    #[\Override]
    public function render(array $data, array $column, string|null $formattedContent = null): string
    {
        $prefix = '';
        if (isset($data['s4']) && $data['s4'] !== null) {
            $prefix = '(Schedule 4/1)';
        }

        $content = parent::render($data, $column, $formattedContent);

        return trim($prefix . ' ' . $content);
    }
}
