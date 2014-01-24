<?php
/**
 * Formats a list of attributes
 *
 * Usable when there's many attributes on an element that are conditional on some PHP-value
 * 
 * @package     OlcsSelfserve
 * @subpackage  view
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace OlcsSelfserve\View\Helper;

use Zend\View\Helper\AbstractHtmlElement;

/**
 * Formats a list of attributes
 */
class FormatAttributes extends AbstractHtmlElement
{
    /**
     * Formats a list of attributes
     *
     * @param  array  $attributes The attributes to convert as an associative array
     * @return string             The formatted attributes
     */
    public function __invoke(array $attributes)
    {
        if (!empty($attributes['data']) && is_array($attributes['data'])) {
            foreach ($attributes['data'] as $key => $data) {
                $attributes['data-' . $key] = $data;
            }
            unset($attributes['data']);
        }

        return $this->htmlAttribs(array_filter($attributes));
    }
}
