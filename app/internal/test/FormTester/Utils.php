<?php

namespace Dvsa\OlcsTest\FormTester;

use Laminas\Form\FieldsetInterface;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Form\Element\Button;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Submit;

/**
 * Class Utils
 *
 * Utility package for the form tester abstract; contains methods which need recursion, placed in their own class for
 * tidiness, should be considered as private functions for the Abstract form tester.
 *
 * @package Olcs\TestHelpers\FormTester
 * @private
 */
class Utils
{
    /**
     * Recursively extracts all field names from a form
     *
     * @param FieldsetInterface $form
     * @return array
     */
    public static function extractFields(FieldsetInterface $form)
    {
        $data = [];
        foreach ($form->getElements() as $element) {
            if (!($element instanceof Button || $element instanceof Submit || $element instanceof Hidden)) {
                $data[$element->getName()] = true;
            }
        }

        foreach ($form->getFieldsets() as $fieldset) {
            $extracted = static::extractFields($fieldset);
            if (!empty($extracted)) {
                $data[$fieldset->getName()] = $extracted;
            }
        }

        return $data;
    }

    /**
     * Performs a recursive array diff and ensures that all differences from both arrays are present in the result
     *
     * @param $a1
     * @param $a2
     * @return array
     */
    public static function fullArrayDiffRecursive($a1, $a2)
    {
        return ArrayUtils::merge(
            static::arrayDiffRecursive($a1, $a2),
            static::arrayDiffRecursive($a2, $a1)
        );
    }

    /**
     * Performs a recursive array diff preferring values from $a1 eg inside sub arrays, keys which are different in $a1
     * and $a2 or keys which are in $a1 and not $a2 are returned, however keys from $a2 which are not in $a1 are not.
     *
     * @param $a1
     * @param $a2
     * @return array
     */
    public static function arrayDiffRecursive($a1, $a2)
    {
        $return = array();

        foreach ($a1 as $key => $value) {
            if (array_key_exists($key, $a2)) {
                if (is_array($value)) {
                    $recursiveDiff = static::arrayDiffRecursive($value, $a2[$key]);
                    if (count($recursiveDiff)) {
                        $return[$key] = $recursiveDiff;
                    }
                } else {
                    if ($value != $a2[$key]) {
                        $return[$key] = $value;
                    }
                }
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     * Flattens a multi dimensional array into a single dimension, intended for use with a set of form/test differences
     *
     * @param $array
     * @param string $prefix
     * @return array
     */
    public static function flatten($array, $prefix = '')
    {
        $return = [];
        $prefix = (($prefix == '') ? $prefix : $prefix . '->');
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $return = array_merge($return, static::flatten($value, $prefix . $key));
            } else {
                $return[$prefix.$key] = $value;
            }
        }

        return $return;
    }
}
