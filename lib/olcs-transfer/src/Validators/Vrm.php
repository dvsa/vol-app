<?php

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator\AbstractValidator;

/**
 * VRM Validator
 *
 * Ensure the VRM matches the required criteria for a VRM.
 */
class Vrm extends AbstractValidator
{
    /**
     * Holds the templates
     *
     * @var array
     */
    protected $messageTemplates = [
        'invalid' => 'vehicle.error.vrm.invalid',
    ];

    /**
     * Exceptional VRMs which are valid but would fail
     * the normal pattern-based checks
     *
     * @var array
     */
    protected $exceptions = [
        '11',
        '1CZS',
        '1G',
        '1S',
        '1RAQ',
        '1V',
        'AH0',
        'BH0521',
        'BL0131',
        'EB02',
        'G0',
        'G1',
        'HS0',
        'IG99',
        'KHW004',
        'KI1',
        'LM0',
        'OS0500',
        'OS0579',
        'QLD1',
        'QTR1',
        'QUE1',
        'RG0',
        'S0',
        'S1',
        'SY0',
        'V0',
        'V1',
        'VS0',
        'ZG',
        'ZV'
    ];

    /**
     * How we map each character (as defined in the DVSA business rules)
     * to a partial regular expression
     *
     * @var array
     */
    protected $characterMap = [
        '1' => '1-9',
        '9' => '0-9',
        'A' => 'A-HJ-PR-Y',     // no I, Q, Z
        'B' => 'A-HJ-NPR-TV-Y', // no I, O, Q U, Z
        'C' => 'A-Z0-9',
        'D' => 'A-Z',
        'E' => 'A-PR-Z',        // no Q
        'Q' => 'Q',
        'Z' => 'Z'
    ];

    /**
     * Valid VRM formats as defined in the DVSA business rules
     *
     * @var array
     */
    protected $validFormats = [
        'EE199',
        'EE1999',
        'EEE1',
        'EEE19',
        'EEE199',
        'EEE1999',
        '1EEE',
        '19EEE',
        '199EE',
        '199EEE',
        '1999EE',
        'A1999',
        'AA19',
        'AA1',
        'A199',
        'A19',
        'A1',
        '1A',
        '1AA',
        '19A',
        '19AA',
        '199A',
        '1999A',
        'B1AAA',
        'B19AAA',
        'B199AAA',
        'AAA1B',
        'AAA19B',
        'AAA199B',
        'DD99DD',
        '99DD99',
        'DD99DDD',
        '999999Z',
        'Q1AAA',
        'Q19AAA',
        'Q199AAA'
    ];

    /**
     * Holds the array of regexes constructed from the DVSA patterns
     */
    protected $patterns = [];

    /**
     * Retrieve a subset of regular expressions which *might* match
     * the given input
     *
     * @param string $input Input
     *
     * @return array
     */
    protected function getPatterns($input)
    {
        if (empty($this->patterns)) {
            $patterns = [];

            foreach ($this->validFormats as $format) {
                // we create a 2D array keyed by length so when we look
                // up a VRM we only run a subset of regexes over it
                $key = strlen($format);
                if (!isset($patterns[$key])) {
                    $patterns[$key] = [];
                }
                $patterns[$key][] = $this->buildRegex($format);
            }
            $this->patterns = $patterns;
        }

        $length = strlen($input);

        // allow for the fact the input might be completely invalid
        if (!isset($this->patterns[$length])) {
            return [];
        }
        return $this->patterns[$length];
    }

    /**
     * Construct a regular expression from a given DVSA format
     *
     * @param string $formatter Formatter
     *
     * @return string
     */
    protected function buildRegex($formatter)
    {
        $pattern = '';
        foreach (str_split($formatter) as $char) {
            $pattern .= '[' . $this->characterMap[$char] . ']';
        }
        return '/' . $pattern . '/';
    }

    /**
     * Check if VRM is valid
     *
     * @param mixed $value Value
     *
     * @return bool
     */
    #[\Override]
    public function isValid($value)
    {
        if (in_array($value, $this->exceptions)) {
            return true;
        }

        $patterns = $this->getPatterns($value);
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        $this->error('invalid');
        return false;
    }
}
