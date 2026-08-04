<?php

namespace Common\View\Helper;

use Common\Module;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Helper\AbstractHelper;
use Laminas\I18n\View\Helper\Translate;

/**
 * Date
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Date extends AbstractHelper
{
    public function __construct(private Translate $translator)
    {
    }

    /**
     * Output the date in a specific format, or alternative if null
     *
     * @param int|null $timestamp
     * @param string $dateFormat
     * @param string $altIfNull
     * @return string
     */
    public function __invoke($timestamp = null, $dateFormat = null, $altIfNull = 'Unknown')
    {
        if (is_null($dateFormat)) {
            $dateFormat = Module::$dateFormat;
        }

        if (empty($timestamp)) {
            $translate = $this->translator;
            return $translate($altIfNull);
        }

        return date($dateFormat, $timestamp);
    }
}
