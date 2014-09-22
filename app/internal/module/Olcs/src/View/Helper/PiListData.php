<?php

namespace Olcs\View\Helper;

use Zend\View\Helper\AbstractHelper;

class PiListData extends AbstractHelper
{
    public function __invoke($data)
    {
        if (is_array($data) && !empty($data)) {
            return implode(',', $this->map($data));
        }

        return 'None selected';
    }

    protected function map($data)
    {
        return array_map(array($this, 'formatItem'), $data);
    }

    protected function formatItem($item) {
        return $item['sectionCode'] . ' ' . $item['description'];
    }
}