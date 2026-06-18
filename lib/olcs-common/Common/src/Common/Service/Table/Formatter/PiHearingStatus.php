<?php

/**
 * PI Hearing Status formatter
 */

namespace Common\Service\Table\Formatter;

/**
 * PI Hearing Status formatter
 */
class PiHearingStatus implements FormatterPluginManagerInterface
{
    /**
     * Format a PI Hearing status
     *
     * @param  array $row
     * @return string
     */
    #[\Override]
    public function format($row, $column = [])
    {
        if (!empty($row['isCancelled']) && ($row['isCancelled'] === 'Y')) {
            $class = 'red';
            $text = 'CNL';
        } elseif (!empty($row['isAdjourned']) && ($row['isAdjourned'] === 'Y')) {
            $class = 'orange';
            $text = 'ADJ';
        }

        return isset($text) ? sprintf('<span class="status %s">%s</span>', $class, $text) : '';
    }
}
