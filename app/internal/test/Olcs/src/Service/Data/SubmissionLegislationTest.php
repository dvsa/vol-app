<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\SubmissionLegislation;

/**
 * Class SubmissionLegislationTest
 * @package OlcsTest\Service\Data
 */
class SubmissionLegislationTest extends AbstractPublicInquiryDataTestCase
{
    private $reasons = [
        ['id' => 12, 'description' => 'Description 1', 'isProposeToRevoke' => 'Y'],
        ['id' => 15, 'description' => 'Description 2', 'isProposeToRevoke' => 'N'],
    ];

    private $reasons2 = [
        ['value' => 12, 'label' => 'Description 1', 'attributes' => ['data-in-office-revokation' => 'Y']],
        ['value' => 15, 'label' => 'Description 2', 'attributes' => ['data-in-office-revokation' => 'N']],
    ];

    /** @var SubmissionLegislation */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new SubmissionLegislation($this->abstractPublicInquiryDataServices);
    }

    public function testFormatData(): void
    {
        $this->assertEquals($this->reasons2, $this->sut->formatData($this->reasons));
    }
}
