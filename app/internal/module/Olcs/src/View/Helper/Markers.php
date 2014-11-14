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
        if (isset($markers[$markerType]) && is_array($markers[$markerType]) && !empty($markers[$markerType])) {
            $markup = '<div class="notice-container">';
            foreach ($markers[$markerType] as $marker) {
                $markup .= '<div class="notice--warning">';
                $content = $this->insertPlaceholders($marker);

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

    private function insertPlaceholders($marker)
    {
        $urlHelper = $this->getView()->plugin('url');

        $content = $marker['content'];
        if (!empty($marker['data']) && is_array($marker['data'])) {
            $contentPlaceholders = [];
            foreach ($marker['data'] as $data) {
                if (isset($data['type']) && $data['type'] == 'url') {
                    array_push(
                        $contentPlaceholders,
                        '<a href="' . $urlHelper(
                            $data['route'],
                            $data['params']
                        ) . '">' . $data['linkText'] .
                        '</a>'
                    );
                }
            }
            if (count($contentPlaceholders) > 0) {
                $content = vsprintf($marker['content'], $contentPlaceholders);
            }
        }
        return $content;
    }
}
