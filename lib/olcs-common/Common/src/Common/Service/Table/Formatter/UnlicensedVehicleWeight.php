<?php

/**
 * Unlicensed Vehicle Weight formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Common\Service\Table\Formatter;

/**
 * Unlicensed Vehicle Weight formatter
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UnlicensedVehicleWeight extends StackValue implements FormatterPluginManagerInterface
{
    /**
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $value = parent::format($data, $column);

        return empty($value) ? '' : $value . ' kg';
    }
}
