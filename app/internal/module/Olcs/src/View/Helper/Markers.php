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
            $markup = '<div class="notice-container">';
            foreach ($markers[$markerType] as $marker) {
                $markup .= '<div class="notice--warning">';
                $markup .= isset($marker['content']) ? $marker['content'] : '';
                $markup .= '</div>';
            }
            $markup .= '</div>';
        }

        return $markup;
    }
}
