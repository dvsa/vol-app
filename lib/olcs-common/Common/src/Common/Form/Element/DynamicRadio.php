<?php

namespace Common\Form\Element;

use Common\Service\Data\PluginManager;

/**
 * Class DynamicRadio
 * @package Common\Form\Element
 */
class DynamicRadio extends ErrorOverrideRadio
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
