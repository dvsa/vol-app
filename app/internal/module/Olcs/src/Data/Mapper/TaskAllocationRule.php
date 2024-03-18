<?php

namespace Olcs\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;
use Common\RefData;

/**
 * Class TaskAllocationRule
 * @package Olcs\Data\Mapper
 */
class TaskAllocationRule implements MapperInterface
{
    /**
     * Should map data from a result array into an array suitable for a form
     *
     * @param array $data
     */
    public static function mapFromResult(array $data)
    {
        if (!isset($data['id'])) {
            // if no ID key then must be new, therefore:
            return [];
        }

        $user = $data['user'];
        // if no user selected and task Alpha splits exists then set the user dropdown to 'alpha-split'
        if (empty($user) && is_array($data['taskAlphaSplits']) && count($data['taskAlphaSplits']) > 0) {
            $user = 'alpha-split';
        }

        $formData = [
            'details' => [
                'id' => $data['id'],
                'version' => $data['version'],
                'category' => $data['category'],
                'subCategory' => $data['subCategory'],
                'goodsOrPsv' => $data['goodsOrPsv']['id'] ?? 'na',
                'isMlh' => $data['isMlh'] ? 'Y' : 'N',
                'trafficArea' => $data['trafficArea'],
                'teamId' => $data['team']['id'],
                'team' => $data['team'],
                'user' => $user,
            ]
        ];

        return $formData;
    }

    /**
     * Should map form data back into a command data structure
     *
     * @param array $formData
     * @return array
     */
    public static function mapFromForm(array $formData)
    {
        $details = $formData['details'];
        $data = [
            'id' => $details['id'],
            'version' => $details['version'],
            'category' => $details['category'],
            'subCategory' => $details['subCategory'],
            'goodsOrPsv' => $details['goodsOrPsv'],
            'trafficArea' => $details['trafficArea'],
            'team' => $details['team'],
            'user' => $details['user'],
        ];

        if ($details['goodsOrPsv'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $data['isMlh'] = $details['isMlh'];
        } else {
            $data['isMlh'] = null;
        }

        if (empty($data['team']) && is_numeric($details['teamId'])) {
            $data['team'] = $details['teamId'];
        }

        // if Alpha Split is selected then set user to null
        if ($data['user'] === 'alpha-split') {
            $data['user'] = null;
        }
        // if selected Not Applicable for goodsOrPsv
        if ($data['goodsOrPsv'] === 'na') {
            $data['goodsOrPsv'] = null;
        }
        if (empty($data['user'])) {
            unset($data['user']);
        }

        return $data;
    }

    /**
     * Should map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form
     * @param array $errors
     * @return array
     * @inheritdoc
     */
    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        return $errors;
    }
}
