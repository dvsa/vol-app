<?php

/**
 * Get Placeholder
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Utils\View\Helper;

use Laminas\View\Model\ViewModel;

/**
 * Get Placeholder
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GetPlaceholder
{
    private $container;

    public function __construct($container = null)
    {
        $this->container = $container;
    }

    protected function getValue()
    {
        return $this->container->getValue();
    }

    public function asString()
    {
        $value = $this->getValue();

        if (is_array($value)) {
            $value = reset($value);
        }

        if (is_string($value)) {
            return $value;
        }

        return null;
    }

    public function asView()
    {
        $value = $this->getValue();

        if (is_array($value)) {
            $value = reset($value);
        }

        if ($value instanceof ViewModel) {
            return $value;
        }

        return null;
    }

    public function asObject()
    {
        $value = $this->getValue();

        if (is_array($value)) {
            $value = reset($value);
        }

        if (is_object($value)) {
            return $value;
        }

        return null;
    }

    public function asBool()
    {
        $value = $this->getValue();

        if (is_array($value)) {
            $value = reset($value);
        }

        if (is_bool($value)) {
            return $value;
        }

        return null;
    }
}
