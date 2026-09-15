<?php

namespace Common\Form\Elements\InputFilters;

use Laminas\InputFilter\InputProviderInterface;

/**
 * Name Required
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class NameRequired extends Name implements InputProviderInterface
{
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
    }

    /**
     * Provide default input rules for this element.
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        $specification = parent::getInputSpecification();
        $specification['required'] = true;

        return $specification;
    }
}
