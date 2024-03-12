<?php

namespace Olcs\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Class PiListData
 * @package Olcs\View\Helper
 */
class PiListData extends AbstractHelper
{
    /**
     * @param $data
     * @return string
     */
    public function __invoke($data)
    {
        if (is_array($data) && !empty($data)) {
            return implode(', ', array_map([$this, 'formatItem'], $data));
        }

        return 'None selected';
    }

    /**
     * @param $item
     * @return string
     */
    protected function formatItem($item)
    {
        return $item['sectionCode'] . ' ' . $item['description'];
    }
}
