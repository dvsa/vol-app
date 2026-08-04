<?php

/**
 * Task date formatter
 *
 * @author Nick payne <nick.payne@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

/**
 * Task date formatter
 *
 * @author Nick payne <nick.payne@valtech.co.uk>
 */
class TaskDate implements FormatterPluginManagerInterface
{
    public function __construct(private Date $dateFormatter)
    {
    }

    /**
     * Format a task date
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $date = $this->dateFormatter->format($data, $column);
        if (isset($data['urgent']) && $data['urgent'] === 'Y') {
            $date .= ' (urgent)';
        }

        if (isset($data['isClosed']) && $data['isClosed'] === 'Y') {
            $date .= ' <span class="status red">closed</span>';
        }

        return $date;
    }
}
