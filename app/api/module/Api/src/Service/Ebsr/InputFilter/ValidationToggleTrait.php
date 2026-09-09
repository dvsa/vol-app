<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\InputFilter;

/**
 * Decides whether the rules validators for an EBSR input should be attached.
 *
 * Validation is only ever switched off for debugging, never in production, so it stays on unless
 * the config holds a value that unambiguously reads as false. A mistyped or string typed setting
 * must not silently detach the validators and let unchecked packs through.
 */
trait ValidationToggleTrait
{
    /**
     * @param array  $config    merged application config
     * @param string $inputName name of the input within the ebsr validate config
     *
     * @return bool
     */
    private function isValidationEnabled(array $config, string $inputName)
    {
        $setting = $config['ebsr']['validate'][$inputName] ?? true;

        //anything we can't read as a boolean leaves validation on
        if (!is_scalar($setting) || $setting === '') {
            return true;
        }

        return filter_var($setting, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }
}
