<?php

declare(strict_types=1);

namespace Common\Service\Table\Formatter;

class ExternalConversationStatus implements FormatterPluginManagerInterface
{
    /**
     * status
     *
     * @param array $row Row data
     * @param array $column Column data
     *
     * @inheritdoc
     */
    #[\Override]
    public function format($row, $column = null): string
    {
        $tagColor = match ($row['userContextStatus']) {
            "NEW_MESSAGE" => 'govuk-tag--red',
            "OPEN" => 'govuk-tag--blue',
            "CLOSED" => 'govuk-tag--grey',
            default => 'govuk-tag--green',
        };

        return sprintf(
            '<strong class="govuk-tag %s">%s</strong>',
            $tagColor,
            ucfirst(strtolower(str_replace('_', ' ', $row['userContextStatus']))),
        );
    }
}
