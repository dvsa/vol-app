<?php

namespace Common\Form\Element;

use Common\Service\Data\PluginManager;
use Laminas\Form\Element\Radio;

/**
 * Class DynamicSelect
 * @package Common\Form\Element
 */
class DynamicRadioHtml extends Radio
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
