<?php

/**
 * Stack Value formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Service\Helper\StackHelperService;
use Common\Util\Escape;

/**
 * Stack Value formatter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class StackValue implements FormatterPluginManagerInterface
{
    public function __construct(private StackHelperService $stackHelper)
    {
    }

    /**
     * Retrieve a nested value, escaped
     *
     * This reaches a table cell with nothing else between it and the page — lva-psv-vehicles maps it
     * onto vehicle->vrm and vehicle->makeModel, lva-safety onto contactDetails->fao — so the value is
     * row data by the table escaping contract and is escaped here.
     *
     * Only stringable values are escaped. null has to survive as null because UnlicensedVehicleWeight
     * distinguishes it from a real weight, and an array is returned untouched rather than handed to
     * Escape::html(), which rejects one outright.
     *
     * @param  array $data
     * @param  array $column
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $value = $this->value($data, $column);

        if (is_scalar($value) || $value instanceof \Stringable) {
            return Escape::html((string)$value);
        }

        return $value;
    }

    /**
     * The nested value as it is stored, for subclasses that compute on it.
     *
     * Escaping happens in format() rather than here because a subclass that does arithmetic on the
     * value has to see it before it becomes markup: NumberStackValue passes it to number_format(),
     * which takes int|float and would reject the escaped string under strict_types.
     *
     * @param  array $data
     * @param  array $column
     */
    protected function value($data, $column = []): mixed
    {
        if (!isset($column['stack'])) {
            throw new \InvalidArgumentException('No stack configuration found');
        }

        if (is_string($column['stack'])) {
            $column['stack'] = explode('->', $column['stack']);
        }

        return $this->stackHelper->getStackValue($data, $column['stack']);
    }
}
