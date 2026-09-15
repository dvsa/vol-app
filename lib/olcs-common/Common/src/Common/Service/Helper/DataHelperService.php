<?php

/**
 * Data Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Helper;

/**
 * Data Helper Service
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DataHelperService
{
    /**
     * Replace the children's array, with their ids
     *
     * @param array $data
     * @return array
     */
    public function replaceIds($data)
    {
        foreach ($data as $key => $var) {
            if (isset($var['id'])) {
                $data[$key] = $var['id'];
            }
        }

        return $data;
    }

    /**
     * Repeat an array x times
     *
     * @param array $array
     * @param int $count
     * @return array
     */
    public function arrayRepeat($array, $count)
    {
        $arrays = [];

        for ($i = 0; $i < $count; ++$i) {
            $arrays[] = $array;
        }

        return $arrays;
    }

    /**
     * Process the data map
     *
     * @param type $data
     */
    public function processDataMap($oldData, $map = [], $section = 'main')
    {
        if (empty($map)) {
            return $oldData;
        }

        if (isset($map['_addresses'])) {
            foreach ($map['_addresses'] as $address) {
                $oldData = $this->processAddressData($oldData, $address);
            }
        }

        $data = [];

        if (isset($map[$section]['mapFrom'])) {
            foreach ($map[$section]['mapFrom'] as $key) {
                if (isset($oldData[$key])) {
                    $data = array_merge($data, $oldData[$key]);
                }
            }
        }

        if (isset($map[$section]['children'])) {
            foreach ($map[$section]['children'] as $child => $options) {
                $data[$child] = $this->processDataMap($oldData, [$child => $options], $child);
            }
        }

        if (isset($map[$section]['values'])) {
            return array_merge($data, $map[$section]['values']);
        }

        return $data;
    }

    /**
     * Find the address fields and process them accordingly
     *
     * @param array $data
     * @return array $data
     */
    private function processAddressData($data, $addressName = 'address')
    {
        if (!isset($data['addresses'])) {
            $data['addresses'] = [];
        }

        unset($data[$addressName]['searchPostcode']);

        if (isset($data[$addressName])) {
            $data['addresses'][$addressName] = $data[$addressName];

            unset($data[$addressName]);
        }

        return $data;
    }

    public function fetchNestedData($data, $search)
    {
        if (str_contains($search, '->')) {
            [$head, $rest] = explode('->', $search, 2);
            return $this->fetchNestedData($data[$head], $rest);
        }

        return $data[$search];
    }

    /**
     * Compare a subset of two arrays, $keys specifying
     * the keys we care about during comparison
     *
     * @param array $from
     * @param array $to
     * @param array $keys
     *
     * @return array
     */
    public function compareKeys($from, $to, $keys)
    {
        $keys = array_flip($keys);
        $from = array_intersect_key($from, $keys);
        $to   = array_intersect_key($to, $keys);

        return array_diff_assoc($to, $from);
    }
}
