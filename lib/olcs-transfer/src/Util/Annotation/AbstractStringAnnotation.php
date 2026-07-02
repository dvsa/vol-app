<?php

namespace Dvsa\Olcs\Transfer\Util\Annotation;

use Laminas\Form\Exception;

class AbstractStringAnnotation
{
    /**
     * @var string
     */
    protected string $value;

    /**
     * Receive and process the contents of an annotation
     *
     * @throws Exception\DomainException if a 'value' key is missing, or its value is not a string
     */
    public function __construct(array $data)
    {
        if (! isset($data['value']) || ! is_string($data['value'])) {
            throw new Exception\DomainException(sprintf(
                '%s expects the annotation to define a string; received "%s"',
                static::class,
                gettype($data['value'])
            ));
        }
        $this->value = $data['value'];
    }
}
