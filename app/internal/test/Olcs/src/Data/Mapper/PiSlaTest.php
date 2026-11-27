<?php

declare(strict_types=1);

namespace OlcsTest\Data\Mapper;

use Olcs\Data\Mapper\PiSla;
use Laminas\Form\FormInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PiSlaTest extends TestCase
{
    #[DataProvider('outcomes')]
    public function testMapFromFormKeepsOnlyDatesForSelectedOutcome(?string $outcome, array $activeFields): void
    {
        $dates = [
            'decisionLetterSentDate' => '2020-01-01',
            'tcWrittenDecisionDate' => '2020-01-02',
            'writtenDecisionLetterDate' => '2020-01-03',
            'tcWrittenReasonDate' => '2020-01-04',
            'writtenReasonLetterDate' => '2020-01-05',
        ];
        $result = PiSla::mapFromForm(['fields' => $dates + [
            'writtenOutcome' => $outcome,
            'id' => 1,
            'callUpLetterDate' => '2020-01-06',
        ]]);

        self::assertSame(
            array_intersect_key($dates, array_flip($activeFields)) + [
                'writtenOutcome' => $outcome,
                'id' => 1,
                'callUpLetterDate' => '2020-01-06',
                'publish' => 'N',
            ],
            $result
        );
    }

    public static function outcomes(): array
    {
        return [
            'verbal' => ['piwo_verbal', ['decisionLetterSentDate']],
            'decision' => ['piwo_decision', ['tcWrittenDecisionDate', 'writtenDecisionLetterDate']],
            'reason' => ['piwo_reason', ['tcWrittenReasonDate', 'writtenReasonLetterDate']],
            'no outcome' => [null, []],
        ];
    }

    public function testNestedApiErrorsProduceAStringFlashMessage(): void
    {
        $form = $this->createStub(FormInterface::class);
        $errors = ['messages' => ['decisionLetterSentDate' => ['dateInvalid' => 'Invalid date']]];

        self::assertSame(['unknown-error'], PiSla::mapFromErrors($form, $errors));
    }

    public function testStringApiErrorsArePreserved(): void
    {
        $form = $this->createStub(FormInterface::class);

        self::assertSame(['error-code'], PiSla::mapFromErrors($form, ['messages' => ['error-code']]));
    }
}
