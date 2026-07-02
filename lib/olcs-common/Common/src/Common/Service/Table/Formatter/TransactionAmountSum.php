<?php

/**
 * Transaction Amount Sum formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\Service\Table\Formatter;

// need to alias as RefData exists in Formatter namespace
use Common\RefData as Ref;

/**
 * Transaction Amount Sum formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransactionAmountSum implements FormatterPluginManagerInterface
{
    public function __construct(private Money $moneyFormatter)
    {
    }

    /**
     * Sums the data of a specific column and formats the result as a fee amount
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $sum = 0;

        foreach ($data as $row) {
            if ($row['status']['id'] === Ref::TRANSACTION_STATUS_COMPLETE) {
                $sum += (float)$row['amount'];
            }
        }

        $data[$column['name']] = $sum;
        return $this->moneyFormatter->format($data, $column);
    }
}
