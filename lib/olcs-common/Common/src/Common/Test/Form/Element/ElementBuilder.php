<?php

declare(strict_types=1);

namespace Common\Test\Form\Element;

use Laminas\Form\Element;

final class ElementBuilder
{
    /**
     * @var string|null
     */
    protected $label;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @return static
     */
    public static function anElement(): self
    {
        return new static();
    }

    public function withLabel(string $label): self
    {
        $instance = $this->clone();
        $instance->label = $label;
        return $instance;
    }

    /**
     * @return $this
     */
    public function withShortLabel(string $label): self
    {
        $instance = $this->clone();
        $instance->options['short-label'] = $label;
        return $instance;
    }

    /**
     * @return $this
     */
    public function withType(string $type): self
    {
        $instance = $this->clone();
        $instance->type = $type;
        return $instance;
    }

    protected function clone(): self
    {
        $instance = new static();
        $instance->label = $this->label;
        $instance->type = $this->type;
        return $instance;
    }

    public function build(): Element
    {
        $element = new Element();

        if (null !== $this->label) {
            $element->setLabel($this->label);
        }

        if (null !== $this->type) {
            $element->setAttribute('type', $this->type);
        }

        $element->setOptions($this->options);

        return $element;
    }
}
