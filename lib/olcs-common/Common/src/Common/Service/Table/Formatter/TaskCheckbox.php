<?php

/**
 * Task checkbox formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Table\TableBuilder;

/**
 * Task checkbox formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TaskCheckbox implements FormatterPluginManagerInterface
{
    public function __construct(private TableBuilder $tableBuilder)
    {
    }

    /**
     * Format a task checkbox
     *
     * @param      array $data
     * @param      array $column
     * @return     string
     * @inheritdoc
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (isset($data['isClosed']) && $data['isClosed'] === 'Y') {
            return '';
        }

        return $this->tableBuilder->replaceContent('{{[elements/checkbox]}}', $data);
    }
}
