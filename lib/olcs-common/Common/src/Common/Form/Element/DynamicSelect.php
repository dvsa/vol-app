<?php

namespace Common\Form\Element;

use Common\Service\Data\PluginManager;
use Laminas\Form\Element\Select;

/**
 * Class DynamicSelect
 * @package Common\Form\Element
 */
class DynamicSelect extends Select
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
