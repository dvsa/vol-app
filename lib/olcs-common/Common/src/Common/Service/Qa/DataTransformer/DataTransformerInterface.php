<?php

namespace Common\Service\Qa\DataTransformer;

interface DataTransformerInterface
{
    /**
     * Convert post data from the frontend to a format suitable for backend submission, for a single fieldset
     *
     *
     * @return array
     */
    public function getTransformed(array $data);
}
