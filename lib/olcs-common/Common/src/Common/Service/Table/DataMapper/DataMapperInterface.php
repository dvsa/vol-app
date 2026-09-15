<?php

/**
 * Provide an interface for data mappers to flatten/convert array data
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Common\Service\Table\DataMapper;

/**
 * Provide an interface for data mappers to flatten/convert array data
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
interface DataMapperInterface
{
    /**
     * Maps array data
     * @return array Flattened/converted data
     */
    public function map(array $data);
}
