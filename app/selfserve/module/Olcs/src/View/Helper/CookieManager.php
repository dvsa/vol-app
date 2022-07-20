<?php

namespace Olcs\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\HelperInterface;

/**
 * Class CookieManagerHelper
 *
 * @package Olcs\View\Helper
 */
class CookieManager extends AbstractHelper implements HelperInterface
{
    /** @var array */
    protected $config;

    /**
     * Create service instance
     *
     * @param array $config
     *
     * @return CookieManager
     */
    public function __construct(
        array $config
    ) {
        $this->config = $config;
    }

    public function __invoke()
    {
        return $this->getConfig('cookie-manager');
    }

    private function getConfig(string $name)
    {
        return json_encode($this->config[$name]);
    }
}
