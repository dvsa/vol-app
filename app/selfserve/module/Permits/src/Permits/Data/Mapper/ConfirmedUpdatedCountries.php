<?php

namespace Permits\Data\Mapper;

/**
 * Confirmed updated countries mapper
 */
class ConfirmedUpdatedCountries
{
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
