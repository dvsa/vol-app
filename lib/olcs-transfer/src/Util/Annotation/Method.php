<?php

/**
 * Method
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Util\Annotation;

/**
 * @Annotation
 */
class Method extends AbstractStringAnnotation
{
    public function getMethod()
    {
        return $this->value;
    }
}
