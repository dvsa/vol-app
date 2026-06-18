<?php

namespace Common\Service\Qa\Custom\Common;

use Common\Form\Elements\Types\Html;
use Laminas\Form\Fieldset;

class HtmlAdder
{
    /**
     * Populate the fieldset with a HTML element containing the specified markup
     *
     * @param string $name
     * @param string $markup
     * @param int $priority (optional)
     */
    public function add(Fieldset $fieldset, $name, $markup, $priority = null): void
    {
        $flags = [];
        if ($priority) {
            $flags['priority'] = $priority;
        }

        $fieldset->add(
            [
                'name' => $name,
                'type' => Html::class,
                'attributes' => [
                    'value' => $markup
                ]
            ],
            $flags
        );
    }
}
