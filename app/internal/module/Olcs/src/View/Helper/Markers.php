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
    public function __invoke($markers, $markerType, $convertNewLines = true)
    {

        $markup = '';
        if (isset($markers[$markerType]) && is_array($markers[$markerType])) {
            $markup = '<div class="notice-container">';
            foreach ($markers[$markerType] as $marker) {
                $markup .= '<div class="notice--warning">';
                $content = isset($marker['content']) ? $marker['content'] : '';

                // split content on new lines
                if ($convertNewLines) {
                    $content = nl2br($content, true);
                }
                $markup .= $content;
                $markup .= '</div>';
            }
            $markup .= '</div>';
        }

        return $markup;
    }
}
