<?php

/**
 * ArrayInput
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Util\Annotation;

/**
 * @Annotation
 */
class ArrayInput
{
    /**
     * @var bool
     */
    protected $arrayInput = false;

    /**
     * Receive and process the contents of an annotation
     */
    public function __construct(array $data)
    {
        if (!isset($data['value'])) {
            $data['value'] = true;
        }

        $this->arrayInput = $data['value'];
    }

    /**
     * Get value of optional flag
     *
     * @return bool
     */
    public function getArrayInput()
    {
        return $this->arrayInput;
    }
}
