<?php

declare(strict_types=1);

namespace Common\Service\Table\Formatter;

class NumberStackValue extends StackValue implements FormatterPluginManagerInterface
{
    /**
     * value(), not parent::format(): number_format() takes int|float and the parent escapes into a
     * string. Its own output needs no escaping — number_format() can only emit digits, separators
     * and a sign whatever it is given.
     */
    #[\Override]
    public function format($data, $column = [])
    {
        return number_format($this->value($data, $column));
    }
}
