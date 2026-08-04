<?php

/**
 * Plain Text Element
 *
 * @author Someone <someone@valtech.co.uk>
 */

namespace Common\Form\Elements\Types;

use Common\Form\Form;
use Traversable;
use Laminas\Form\Element;
use Laminas\Form\ElementInterface;

/**
 * Plain Text Element
 *
 * @author Someone <someone@valtech.co.uk>
 */
class PlainText extends Element
{
    /**
     * Seed attributes
     *
     * @var array
     */
    protected $attributes = [
        'type' => 'plaintext',
    ];

    /**
     * setValue
     *
     * @param mixed $value value
     */
    #[\Override]
    public function setValue($value): void
    {
        /**  #OLCS-17989 - overridden to ensure any injection cannot happen **/
        if (!Form::isPopulating()) {
            parent::setValue($value);
        }
    }

    /**
     * Set options for an element. Accepted options are:
     * - label: label to associate with the element
     * - label_attributes: attributes to use when the label is rendered
     *
     * @param  array|Traversable $options
     *
     * @return Element|ElementInterface
     */
    #[\Override]
    public function setOptions($options)
    {
        parent::setOptions($options);

        if (isset($options['value'])) {
            $this->setValue($options['value']);
        }

        return $this;
    }
}
