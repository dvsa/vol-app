<?php

/**
 * Fee Number with Status formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\Service\Table\Formatter;

/**
 * Fee Number with Status formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeNoAndStatus implements FormatterPluginManagerInterface
{
    public function __construct(private FeeStatus $feeStatusFormatter)
    {
    }

    /**
     * Format a fee status
     *
     * @param      array $row
     * @param      array $column
     * @return     string
     * @inheritdoc
     */
    #[\Override]
    public function format($row, $column = null)
    {
        return $row['id'] . ' ' . $this->feeStatusFormatter->format($row);
    }
}
