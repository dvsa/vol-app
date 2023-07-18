<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\MapperInterface;

/**
 * Confirmed updated countries mapper
 */
class ConfirmedUpdatedCountries implements MapperInterface
{
    use MapFromResultTrait;

    /**
     * @param array $data
     *
     * @return array
     */
    public function mapFromForm($data)
    {
        $fields = $data['fields'];
        $fields['countries'] = explode(',', $fields['countries']);

        return $fields;
    }
}
