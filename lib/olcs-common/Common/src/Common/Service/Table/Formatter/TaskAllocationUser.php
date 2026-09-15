<?php

namespace Common\Service\Table\Formatter;

/**
 * User value for a task allocation rule
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TaskAllocationUser extends Name implements FormatterPluginManagerInterface
{
    /**
     * User value for a task allocation rule
     *
     * @param array                                  $data
     * @param array                                  $column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $userName = parent::format($data, $column);
        if (trim($userName) !== '' && trim($userName) !== '0') {
            return $userName;
        }
        if (!is_array($data['taskAlphaSplits'])) {
            return 'Unassigned';
        }
        if (count($data['taskAlphaSplits']) <= 0) {
            return 'Unassigned';
        }
        return '[Alpha split]';
    }
}
