<?php

declare(strict_types=1);

namespace PermitsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Permits\Data\Mapper\SelectedCandidatePermits;

/**
 * SelectedCandidatePermitsTest
 */
class SelectedCandidatePermitsTest extends TestCase
{
    public function testMapFromForm(): void
    {
        $data = [
            'fields' => [
                'otherKey1' => 'otherValue1',
                'candidate-123' => '1',
                'otherKey2' => 'otherValue2',
                'candidate-456' => '0',
                'otherKey3' => 'otherValue3',
                'candidate-789' => '1',
            ]
        ];

        $expected = [
            'otherKey1' => 'otherValue1',
            'candidate-123' => '1',
            'otherKey2' => 'otherValue2',
            'candidate-456' => '0',
            'otherKey3' => 'otherValue3',
            'candidate-789' => '1',
            'selectedCandidatePermitIds' => ['123', '789'],
        ];

        $selectedCandidatePermits = new SelectedCandidatePermits();

        $this->assertEquals(
            $expected,
            $selectedCandidatePermits->mapFromForm($data)
        );
    }
}
