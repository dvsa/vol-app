<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;

class Text implements ElementInterface
{
    /**
     * Create instance
     *
     * @param TranslateableText $label (optional)
     * @param TranslateableText $hint (optional)
     * @param string $value
     *
     * @return Text
     */
    public function __construct(private $value, private readonly ?TranslateableText $label = null, private readonly ?TranslateableText $hint = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepresentation()
    {
        $representation = [
            'value' => $this->value
        ];

        if (!is_null($this->label)) {
            $representation['label'] = $this->label->getRepresentation();
        }

        if (!is_null($this->hint)) {
            $representation['hint'] = $this->hint->getRepresentation();
        }

        return $representation;
    }

    /**
     * Get the embedded hint TranslateableText instance
     *
     * @return TranslateableText|null
     */
    public function getHint()
    {
        return $this->hint;
    }
}
