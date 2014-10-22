<?php

namespace Olcs\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Class Markers
 * @package Olcs\View\Helper
 */
class Markers extends AbstractHelper
{
    /**
     * @param $data
     * @return string
     */
    public function __invoke($markers, $markerType)
    {
        $markup = '';
        if (isset($markers[$markerType]) && is_array($markers[$markerType])) {
            foreach ($markers[$markerType] as $marker) {
                $markup .= isset($marker['content']) ? $marker['content'] : '';
            }
        }

        return $markup;
    }
}
