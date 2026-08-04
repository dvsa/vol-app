<?php

/**
 * Fee Status formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\Service\Table\Formatter;

use Common\RefData;

/**
 * Fee Status formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeeStatus implements FormatterPluginManagerInterface
{
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
        $statusClass = match ($row['feeStatus']['id']) {
            RefData::FEE_STATUS_PAID => 'green',
            RefData::FEE_STATUS_OUTSTANDING => 'orange',
            RefData::FEE_STATUS_CANCELLED => 'red',
            default => 'grey',
        };

        return vsprintf(
            '<strong class="govuk-tag govuk-tag--%s">%s</strong>',
            [$statusClass, $row['feeStatus']['description']]
        );
    }
}
