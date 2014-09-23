<?php

namespace Olcs\View\Helper;

use Zend\View\Helper\AbstractHelper;

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
            return implode(', ', $this->map($data));
        }

        return 'None selected';
    }

    /**
     * @param $data
     * @return array
     */
    protected function map($data)
    {
        return array_map(array($this, 'formatItem'), $data);
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
