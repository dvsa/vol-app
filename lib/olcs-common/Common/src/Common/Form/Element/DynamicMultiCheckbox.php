<?php

namespace Common\Form\Element;

use Common\Service\Data\PluginManager;
use Laminas\Form\Element\MultiCheckbox;

class DynamicMultiCheckbox extends MultiCheckbox
{
    use DynamicTrait;

    public function __construct(
        PluginManager $dataServiceManager,
        $name = null,
        iterable $options = []
    ) {
        $this->dataServiceManager = $dataServiceManager;
        parent::__construct($name, $options);
    }
}
