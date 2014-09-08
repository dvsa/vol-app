<?php

namespace Olcs\View\Helper;

use Zend\View\Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Helper\Placeholder\Container\AbstractStandalone;

/**
 * Helper for setting and retrieving title element for HTML head
 */
class AbstractWidget extends AbstractStandalone implements ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    /**
     * Flag whether to automatically escape output, must also be
     * enforced in the child class if __toString/toString is overridden
     *
     * @var bool
     */
    protected $autoEscape = false;

    /**
     * Registry key for placeholder
     *
     * @var string
     */
    protected $regKey = __CLASS__;

    /**
     * Invokes the view helper.
     *
     * @param  string $content
     * @return AbstractWidget
     */
    public function __invoke($content = null)
    {
        if (null !== $content) {
            $this->set($content);
        }

        return $this;
    }
}
