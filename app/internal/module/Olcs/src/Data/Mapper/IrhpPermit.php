<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

/**
 * Class IrhpPermit
 * @package Olcs\Data\Mapper
 */
class IrhpPermit implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     * @return array
     */
    public static function mapFromResult(array $data)
    {
        if (!empty($data['irhpPermitRange']['countrys'])) {
            $str = '<div class="article"><ul>';
            foreach ($data['irhpPermitRange']['countrys'] as $country) {
                $str .= "<li>{$country['countryDesc']}</li>";
            }
            $str .= '</ul></div>';
            $data['restrictedCountries'] = $str;
        } elseif (!empty($data['irhpPermitRange']['irhpPermitStock']['country'])) {
            $str = '<div class="article"><ul>';
            $str .= "<li>{$data['irhpPermitRange']['irhpPermitStock']['country']['countryDesc']}</li>";
            $str .= '</ul></div>';
            $data['country'] = $str;
        }
        return $data;
    }
}
