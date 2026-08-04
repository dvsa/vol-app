<?php

declare(strict_types=1);

namespace Common\Service\Table\Formatter;

class NumberStackValue extends StackValue implements FormatterPluginManagerInterface
{
    #[\Override]
    public function format($data, $column = [])
    {
        return number_format(parent::format($data, $column));
    }
}
