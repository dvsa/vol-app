<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait PostData Optional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait PostDataOptional
{
    /**
     * @Transfer\Optional()
     * @Transfer\ArrayInput
     */
    protected $postData = [];

    /**
     * @return array
     */
    public function getPostData()
    {
        return $this->postData;
    }
}
