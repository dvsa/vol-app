<?php

namespace Olcs\Data\Mapper;

use Laminas\Form\FormInterface;
use Olcs\Logging\Log\Logger;

class PiSla extends Pi
{
    public static function mapFromForm(array $data)
    {
        $data = parent::mapFromForm($data);
        $outcome = $data['writtenOutcome'] ?? null;

        $outcomeFields = [
            'decisionLetterSentDate' => 'piwo_verbal',
            'tcWrittenDecisionDate' => 'piwo_decision',
            'writtenDecisionLetterDate' => 'piwo_decision',
            'tcWrittenReasonDate' => 'piwo_reason',
            'writtenReasonLetterDate' => 'piwo_reason',
        ];
        foreach ($outcomeFields as $field => $expectedOutcome) {
            if ($outcome !== $expectedOutcome) {
                unset($data[$field]);
            }
        }

        return $data;
    }

    public static function mapFromErrors(FormInterface $form, array $errors)
    {
        $messages = $errors['messages'] ?? [];
        if (is_array($messages) && $messages !== [] && count(array_filter($messages, 'is_string')) === count($messages)) {
            return array_values($messages);
        }

        Logger::debug('unknown-error', $errors);
        return ['unknown-error'];
    }
}
