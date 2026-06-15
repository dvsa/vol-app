<?php

namespace Dvsa\Olcs\Utils\View\Factory\Helper;

use Laminas\View\Helper\AbstractHelper;
use Laminas\View\Helper\HelperInterface;

class GetPlaceholder extends AbstractHelper implements HelperInterface
{
    private $placeholder;
    private $containers = [];

    public function __construct($placeholder)
    {
        $this->placeholder = $placeholder;
    }

    public function __invoke($name)
    {
        if (!isset($this->containers[$name])) {
            $this->containers[$name] = $this->placeholder->__invoke($name);
        }
        return $this->containers[$name];
    }
}
