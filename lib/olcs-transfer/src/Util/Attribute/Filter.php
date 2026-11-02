<?php

namespace Dvsa\Olcs\Transfer\Util\Attribute;

use Attribute;
use Laminas\Form\Annotation\Filter as LaminasFilter;

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
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
