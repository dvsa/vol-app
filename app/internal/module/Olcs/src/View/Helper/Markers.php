<?php

namespace Olcs\View\Helper;

use Laminas\View\Helper\AbstractHelper;

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
                // style should be one of 'success'|'warning'|'info'|'danger', default is 'warning'
                $type = $marker['style'] ?? 'warning';
                $markup .= '<p class="notice--'.$type.'">';
                $content = $this->insertPlaceholders($marker);

                // make first line bold
                $content = explode("\n", $content);
                $content[0] = '<b>'.$content[0].'</b>';
                $content = implode("\n", $content);

                // split content on new lines
                if ($convertNewLines) {
                    $content = nl2br($content, true);
                }
                $markup .= $content;
                $markup .= '</p>';
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
                        '<a ' .
                        (isset($data['class']) ? 'class="'. $data['class'] . '" ' : 'class="govuk-link"') .
                        'href="' . $urlHelper($data['route'], $data['params']) . '">' . $data['linkText'] .
                        '</a>' . "\n"
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
