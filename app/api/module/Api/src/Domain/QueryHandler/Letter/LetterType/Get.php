<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterType;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Get LetterType by ID
 */
class Get extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'LetterType';

    protected $bundle = [
        'masterTemplate',
        'category',
        'subCategory',
        'letterTestData',
        'letterTypeSections' => [
            // currentVersion carries the section's name -- letter_section itself has only a key,
            // so without this every section reads as "001", "002" wherever it is listed.
            'letterSection' => [
                'currentVersion',
            ],
        ],
        'letterTypeIssues',
        'letterTypeAppendices' => [
            'letterAppendixVersion' => [
                'letterAppendix'
            ]
        ],
        'letterTypeChoices' => [
            'letterChoice',
        ],
    ];
}
