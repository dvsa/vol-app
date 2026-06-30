<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait PostData
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
trait PostData
{
    /**
     * @Transfer\ArrayInput
     */
    protected $postData;

    /**
     * @return array
     */
    public function getPostData()
    {
        return $this->postData;
    }
}
