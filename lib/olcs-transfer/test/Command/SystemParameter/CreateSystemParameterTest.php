<?php

namespace Dvsa\OlcsTest\Transfer\Command\SystemParameter;

use Dvsa\Olcs\Transfer\Command\SystemParameter\CreateSystemParameter;
use Dvsa\OlcsTest\Transfer\Command\CommandTest;
use Dvsa\OlcsTest\Transfer\DtoTest;
use Laminas\Stdlib\ArraySerializableInterface;

/**
 * Class CreateSystemParameterTest
 *
 * @package Dvsa\OlcsTest\Transfer\Command\SystemParameter
 */
class CreateSystemParameterTest extends \PHPUnit\Framework\TestCase
{
    use CommandTest;

    /**
     * Should return a new blank DTO on which to run tests
     *
     * @return ArraySerializableInterface
     */
    protected function createBlankDto()
    {
        return new CreateSystemParameter();
    }

    /**
     * Should return a list of optional fields
     *
     * for example:
     *
     * return ['optionalField', 'anotherOptionalField']
     *
     * Each field is expected to be set to null after validation
     *
     * @return string[]
     */
    protected function getOptionalDtoFields()
    {
        return [
            'description',
        ];
    }

    /**
     * Should return an array of valid field values (i.e. those which should pass validation)
     *
     * for example:
     *
     * return [
     *     'fieldName' => [
     *         'good-value-1',
     *         'good-value-2',
     *     ],
     *     'anotherFieldName' => ['good-value'],
     * ];
     *
     * @return array
     */
    protected function getValidFieldValues()
    {
        return [
            'id' => ['1', '2', 'string'],
            'paramValue' => ['1', '2', 'string'],
            'description' => ['this', '', 'longer string'],
        ];
    }

    /**
     * Should return an array of invalid field values (i.e. those which should fail validation)
     *
     * for example:
     *
     * return [
     *     'fieldName' => [
     *         'bad-value-1',
     *         'bad-value-2',
     *     ],
     *     'anotherFieldName' => ['bad-value'],
     * ];
     *
     * @return array
     */
    protected function getInvalidFieldValues()
    {
        return [
            'id' => [
                ['abcdefghijklmnopqrstuvwxyz1234567'],
                ['array']
            ],
            'paramValue' => [
                ['array'],
                'xyTyvpoomjZyHcDi5s5Vzpr10LegVRqsbiyYj7Ij6q6nxSrBkKg8P8ZfEZWGGocoQy2HcVJgy2ISm2AAOlvFy1NNrD4a72HuR7PG22B2oaMhdTCLpOuOCRwgqaVGkLK56W1EVdKc4O44AFvNCz6R3S8n1wUlvBqKdBSsHIHz7jhWbvcTeH7RLjjZI2bRheVeRJFZJ02LZifkXkuIKw2H5EHlNaWTgsT0rUobouvxqTXaLfXK9tLKy8dshJSxjBjOGrTjeZRD2DSq8LrjOL810EvrmheeztIMWAIgNzECaisszuJdeyYLXAhlH8lTaa4MZoUaWNifFf86f2xsUoXmOzN32wu2BkeNqMC4kdquij4YpBBztJPoTr4a8sCGXG5w0mj9ugr7S6m8JfWS0m7YAKrdMnIYvW0czdkqkWilIUs35kbktEHVAaCqYcUbGCXH9Dju77uhsO43wyRmfLykzLa7rvrQwRB0jjqPohL2vmAHzoFC0e4MCYBuvNP4qgAH9YoDVnFGSYB6OJwbevzhw5wVCib0iwzCEc07e2UBBG1wXsjcY3GzcBy7HTMVl4tZaNUcR9wRf0OVAsZIV0pO56cCevesThoWwNC3I3ag0d914F9yEbREqJdPuSwjMGt1EXTqCBGJ3nR9aP1nhN2VnKQg8sY13pbe3iG1qalnNBW5RWjOD1gSm2qCPcOaaafmwJMm8a8fbJW5Ju8OCk1mqAq0B1vvWOdtwpQ0dEERQzQ0HXnmGbdRZBjtVNkgYL7PQXqxPSCvDeRtzXplJItoeqgTU1hl3nh1DWEZl43vWK93PUT7VjWukGdea84HaaNtQJglz4haS9MIZJfqfkEsNmoKrU6Z0OG4xrzE8H6FDSGrieyoVa1MK3rPW7YZMLDamAVHXReeStAjqzZ2Ay3ZX3he2gtmFUnBAjlMwKSSvDP9aBZWzdOlErMcSBlpfioaHQT48FixLscaL4MvE7jLmdk1rST4YR7xBmePwM2AaqJWufX7mrxtW2T06VpXBPqKG'
            ],
            'description' => [
                'sc0jTBfWcUD7UqpVRtM3CbKyOyRAfVTP0LgvEuKrBhQeIHHlft6yG5zCk4lUyHXFZw5XRhKV8Gt14jEXN3Szv5RXusbpDSHc0d6hxz9eGwQjImEoCkdQ3e6eKIlbahzI7cOyuyaaKxNFtgM3Xq62zVCBKdppLoTi9pVj46DmFjsQVn6y98s9H43JuLRT8tAZoTs5yuT93HmeUvJqImjP4N9RAxIMSt6jh4bVoLJBaKfpl3prKmPDZgHl1ClO3k6S',
                ['array']
            ],
        ];
    }

    /**
     * Should return an array of expected transformations which input filters should apply to fields
     *
     * for example:
     *
     * return [
     *     'fieldWhichGetsTrimmed' => [[' string ', 'string']],
     *     'fieldWhichFiltersOutNonNumericDigits => [
     *         ['a1b2c3', '123'],
     *         [99, '99'],
     *     ],
     * ];
     *
     * Tests expect the function 'getFoo' to exist for the function 'foo'.
     *
     * This DOES NOT assert that the value gets validated. To do that @return array
     * @see DtoTest::getValidFieldValues
     *
     */
    protected function getFilterTransformations()
    {
        return [
            'id' => [[' string', 'string'], [33, '33']],
            'paramValue' => [[' string', 'string'], [1, '1']],
            'description' => [[' string ', 'string'], [222, '222']]
        ];
    }
}
