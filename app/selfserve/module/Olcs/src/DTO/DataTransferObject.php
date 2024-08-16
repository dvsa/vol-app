<?php

namespace Olcs\DTO;

class DataTransferObject
{
    /**
     * @var array
     */
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}
