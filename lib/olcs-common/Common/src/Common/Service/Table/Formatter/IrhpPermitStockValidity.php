<?php

namespace Common\Service\Table\Formatter;

/**
 * IRHP Permit Stock table - Validity Period column formatter
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class IrhpPermitStockValidity implements FormatterPluginManagerInterface
{
    public function __construct(private Date $dateFormatter)
    {
    }

    /**
     * Format
     *
     * Returns a formatted date of the Validity Period
     *
     * @param array $data
     * @param array $column
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        if (is_null($data['validFrom']) || is_null($data['validTo'])) {
            return 'N/A';
        }

        $validFrom = $this->dateFormatter->format(['validFrom' => $data['validFrom']], $column);
        $validTo = $this->dateFormatter->format(['validFrom' => $data['validTo']], $column);

        return $validFrom .  " to " . $validTo;
    }
}
