<?php

/**
 * Fee Number with Status formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

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
        // The fee status formatter returns its own markup and escapes its own values; only the id
        // is interpolated raw here.
        return Escape::html($row['id']) . ' ' . $this->feeStatusFormatter->format($row);
    }
}
