<?php

/**
 * DoNotExchange
 *
 * Using this annotation on a Query property will prevent AbstractQuery exchangeArray method to set the property
 *
 * You'll need to use this together with the Transfer\Optional annotation
 */

namespace Dvsa\Olcs\Transfer\Util\Annotation;

/**
 * @Annotation
 */
class DoNotExchange
{
    /**
     * @var bool
     */
    protected $doNotExchange = true;

    /**
     * Receive and process the contents of an annotation
     */
    public function __construct(array $data)
    {
        if (!isset($data['value'])) {
            $data['value'] = true;
        }

        $this->doNotExchange = $data['value'];
    }

    /**
     * Get value of DoNotExchange flag
     *
     * @return bool
     */
    public function getDoNotExchange()
    {
        return $this->doNotExchange;
    }
}
