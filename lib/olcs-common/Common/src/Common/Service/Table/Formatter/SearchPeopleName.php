<?php

namespace Common\Service\Table\Formatter;

use Common\Util\Escape;

class SearchPeopleName implements FormatterPluginManagerInterface
{
    /**
     *
     * @param array $data   The row data.
     * @param array $column The column data.
     *
     * @return string
     */
    #[\Override]
    public function format($data, $column = [])
    {
        return Escape::html($data['personFullname']);
    }
}
