<?php

namespace Permits\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Link back to the permits overview page for that id
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class FeeCalculator extends AbstractHelper
{

    /**
     * Return a formatted Monetary Value
     *
     * @param string|null $value Parameters
     *
     * @return string
     */
    public function __invoke(?string $value): string
    {
        if ((substr($value, strlen($value) - 2) === '00') && (strlen($value) > 0)) {
            return sprintf("£" . $this->escapeHtml(substr($value, 0, strlen($value) - 3)));
        }

        return sprintf("£" . $value);
    }
}
