<?php

namespace Common\Service\Table\Formatter;

/**
 * Data Retention Boolean formatter
 */
class DataRetentionRuleActionType implements FormatterPluginManagerInterface
{
    /**
     * Format
     *
     * @param array $data Data of current row
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        return htmlspecialchars($data['actionType']['id']);
    }
}
