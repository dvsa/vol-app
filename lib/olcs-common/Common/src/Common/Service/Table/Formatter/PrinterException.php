<?php

/**
 * Printer Exception formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Common\Service\Table\Formatter;

/**
 * Printer Exception formatter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrinterException implements FormatterPluginManagerInterface
{
    /**
     * @param array $data   The row data.
     * @param array $column The column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (!$data['user']) {
            $exception = $data['team']['name'];
        } else {
            $exception = isset($data['user']['contactDetails']['person']['forename']) &&
            isset($data['user']['contactDetails']['person']['familyName']) ?
                $data['user']['contactDetails']['person']['forename'] . ' ' .
                $data['user']['contactDetails']['person']['familyName'] : $data['user']['loginId'];
        }

        return $exception;
    }
}
