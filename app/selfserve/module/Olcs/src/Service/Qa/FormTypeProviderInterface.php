<?php

namespace Olcs\Service\Qa;

interface FormTypeProviderInterface
{
    /**
     * Get a Form object instance based on the provided data and validators
     *
     * @param array $data
     * @param array $validators
     *
     * @return mixed
     */
    public function get(array $data, array $validators);
}
