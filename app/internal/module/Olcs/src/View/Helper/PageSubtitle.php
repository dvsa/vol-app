<?php
/**
 * Page PageSubtitle View Helper
 *
 * The reason for this is to ensure that a page title is muddying
 * the code. Previously, almost every controller action was
 * setting the page title and subtitle, then manually passing it into the view.
 * View helpers are the correct method is Zend framework.
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\View\Helper;

use Olcs\View\Helper\PageTitle;

/**
 * Helper for setting and retrieving page subtitle.
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class PageSubtitle extends PageTitle
{
    /**
     * Registry key for placeholder
     *
     * @var string
     */
    protected $regKey = __CLASS__;

    /**
     * Render title (wrapped by title tag)
     *
     * @param  string|null $indent
     * @return string
     */
    public function toString($indent = null)
    {
        $indent = (null !== $indent)
                ? $this->getWhitespace($indent)
                : $this->getIndent();

        $output = $this->renderTitle();

        return $indent . '<h4 class="page-header__subtitle">' . $output . '</h4>';
    }
}
