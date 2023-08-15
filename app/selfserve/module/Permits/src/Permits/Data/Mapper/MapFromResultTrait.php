<?php

namespace Permits\Data\Mapper;

trait MapFromResultTrait
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data Data from command
     *
     * @return array
     */
    public static function mapFromResult(array $data): array
    {
        return ['fields' => $data];
    }
}
