<?php

/**
 * Escape
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Util\Annotation;

/**
 * @Annotation
 */
class Escape
{
    /**
     * @var bool
     */
    protected $escape = true;

    /**
     * Receive and process the contents of an annotation
     */
    public function __construct(array $data)
    {
        if (!isset($data['value'])) {
            $data['value'] = true;
        }

        $this->escape = $data['value'];
    }

    /**
     * Get value of escape flag
     *
     * @return bool
     */
    public function getEscape()
    {
        return $this->escape;
    }
}
