<?php

namespace Dvsa\Olcs\Email\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Provide inline email CSS styles
 */
class EmailStyle extends AbstractHelper
{
    /**
     * Get CSS for a primrary button
     *
     * @return string
     */
    public function primaryButton()
    {
        return 'background-color: #00823b; color: #fff; border-color: #004f24; display: inline-block; ' .
            'vertical-align: top; font-size: 1rem; padding: 0.5em 0.75em; margin-right: 0.3em; text-decoration: none; ' .
            'text-rendering: optimizeLegibility; cursor: pointer; border-bottom: 2px solid; padding-bottom: 0.4em; ' .
            'line-height: 1.4;';
    }
}
