<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text;

use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;

class TextFactory
{
    /**
     * Create and return a Text instance
     *
     * @param TranslateableText $label (optional)
     * @param TranslateableText $hint (optional)
     * @param string $value
     *
     * @return Text
     */
    public function create($value, ?TranslateableText $label = null, ?TranslateableText $hint = null)
    {
        return new Text($value, $label, $hint);
    }
}
