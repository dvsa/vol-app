<?php

/**
 * Nullable Number formatter
 *
 * @author Jason de Jonge <jason.de-jonge@capgemini.co.uk>
 */

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

/**
 * Nullable Number formatter
 *
 * @author Jason de Jonge <jason.de-jonge@capgemini.co.uk>
 */
class NullableNumber implements FormatterPluginManagerInterface
{
    /**
     * Transforms null into 0 for display
     *
     * @param  array $data
     * @param  array $column
     * @return int either the input number or 0 for null values
     */
    #[\Override]
    public function format($data, $column = [])
    {
        $name = $data[$column['name']];
        if (!is_null($name)) {
            return Escape::html($name);
        }

        return 0;
    }
}
