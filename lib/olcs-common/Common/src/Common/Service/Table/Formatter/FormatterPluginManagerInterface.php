<?php

namespace Common\Service\Table\Formatter;

interface FormatterPluginManagerInterface
{
    public function format(array $data, array $column = []);
}
