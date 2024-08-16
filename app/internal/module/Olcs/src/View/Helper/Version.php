<?php

namespace Olcs\View\Helper;

use Laminas\View\Helper\AbstractHelper;

/**
 * Class Version
 * @package Olcs\View\Helper
 */
class Version extends AbstractHelper
{
    /**
     * @var string
     */
    private $version;

    /**
     * Version constructor.
     *
     * @param string $version Version number for internal
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * View helper response
     *
     * @return string
     */
    public function __invoke()
    {
        return trim(preg_replace('/\s+/', ' ', $this->version));
    }
}
