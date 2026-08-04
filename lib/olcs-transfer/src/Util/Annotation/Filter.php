<?php

/**
 * Filter
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Util\Annotation;

use Laminas\Form\Annotation\Filter as LaminasFilter;

/**
 * @Annotation
 * @NamedArgumentConstructor
 */
class Filter
{
    protected LaminasFilter $filter;

    public function __construct($name, array $options = [], ?int $priority = null)
    {
        $this->filter = new LaminasFilter($name, $options, $priority);
    }

    public function __call($name, $arguments)
    {
        return $this->filter->{$name}($arguments);
    }

    public function getName()
    {
        $spec = $this->filter->getFilterSpecification();

        return $spec['name'];
    }

    public function getOptions()
    {
        $spec = $this->filter->getFilterSpecification();

        if (empty($spec['options'])) {
            return null;
        }

        return $spec['options'];
    }
}
