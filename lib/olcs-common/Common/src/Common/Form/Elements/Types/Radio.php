<?php

namespace Common\Form\Elements\Types;

use Common\Form\Element\ErrorOverrideRadio;
use Common\View\Helper\UniqidGenerator;

/**
 * Radio form element
 */
class Radio extends ErrorOverrideRadio
{
    /** @var UniqidGenerator */
    protected $idGenerator;

    public function __construct($name = null, $options = [], UniqidGenerator $idGenerator = null)
    {
        $this->idGenerator = $idGenerator ?? new UniqidGenerator();
        parent::__construct($name, $options);
    }

    /**
     * Set element name, override to set id
     *
     * @param string $name Name of radio element
     */
    #[\Override]
    public function setName($name): void
    {
        parent::setName($name);

        if (empty($this->getAttribute('id'))) {
            $this->setAttribute('id', $name);
        }
    }

    /**
     * Override parent to set attributes required
     *
     * @param array $options Options
     */
    #[\Override]
    public function setValueOptions(array $options): void
    {
        foreach ($options as $key => &$optionSpec) {
            if (is_scalar($optionSpec)) {
                $optionSpec = [
                    'label' => $optionSpec,
                    'value' => $key
                ];
            }

            if (!isset($optionSpec['attributes'])) {
                $optionSpec['attributes'] = [];
            }

            $id = $optionSpec['attributes']['id'] ?? $this->idGenerator->generateId() . '_' . $optionSpec['value'];

            $defaultAttributes = [
                'id' => $id,
                'data-show-element' => sprintf('#%s_content', $id),
            ];

            $optionSpec['attributes'] = array_merge($defaultAttributes, $optionSpec['attributes']);
        }

        parent::setValueOptions($options);
    }
}
