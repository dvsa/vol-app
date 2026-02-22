<?php

declare(strict_types=1);

namespace Admin\Data\Mapper\Letter;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

class LetterIssue implements MapperInterface
{
    /**
     * Map data from a result array into an array suitable for a form
     * For a versioned entity, we get data from the current version
     *
     * @param array $data Data from query
     * @return array
     */
    public static function mapFromResult(array $data): array
    {
        // Get data from the current version if it exists
        $currentVersion = $data['currentVersion'] ?? [];

        $formData = [
            'letterIssue' => [
                'id' => $data['id'] ?? null,
                'issueKey' => $currentVersion['issueKey'] ?? $data['issueKey'] ?? null,
                'heading' => $currentVersion['heading'] ?? $data['heading'] ?? null,
                'category' => $currentVersion['category']['id'] ?? $data['category']['id'] ?? null,
                'subCategory' => $currentVersion['subCategory']['id'] ?? $data['subCategory']['id'] ?? null,
                'goodsOrPsv' => $currentVersion['goodsOrPsv']['id'] ?? $data['goodsOrPsv']['id'] ?? null,
                'letterIssueType' => $currentVersion['letterIssueType']['id'] ?? $data['letterIssueType']['id'] ?? null,
                'defaultBodyContent' => $currentVersion['defaultBodyContent'] ?? $data['defaultBodyContent'] ?? null,
                'isNi' => $currentVersion['isNi'] ?? $data['isNi'] ?? false,
                'requiresInput' => $currentVersion['requiresInput'] ?? $data['requiresInput'] ?? false,
                'minLength' => $currentVersion['minLength'] ?? $data['minLength'] ?? null,
                'maxLength' => $currentVersion['maxLength'] ?? $data['maxLength'] ?? null,
                'helpText' => $currentVersion['helpText'] ?? $data['helpText'] ?? null,
                'publishFrom' => $currentVersion['publishFrom'] ?? $data['publishFrom'] ?? null,
            ]
        ];

        // Convert datetime string to array for DateTimeSelect field
        if (!empty($formData['letterIssue']['publishFrom'])) {
            $publishFrom = new \DateTime($formData['letterIssue']['publishFrom']);
            $formData['letterIssue']['publishFrom'] = [
                'year' => $publishFrom->format('Y'),
                'month' => $publishFrom->format('m'),
                'day' => $publishFrom->format('d'),
                'hour' => $publishFrom->format('H'),
                'minute' => $publishFrom->format('i'),
                'second' => $publishFrom->format('s'),
            ];
        }

        return $formData;
    }

    /**
     * Map form data back into a command data structure
     *
     * @param array $data Data from form
     * @return array
     */
    public static function mapFromForm(array $data): array
    {
        $commandData = $data['letterIssue'] ?? [];

        // Convert boolean values
        if (isset($commandData['isNi'])) {
            $commandData['isNi'] = (bool) $commandData['isNi'];
        }

        if (isset($commandData['requiresInput'])) {
            $commandData['requiresInput'] = (bool) $commandData['requiresInput'];
        }

        // Handle empty selections
        if (empty($commandData['category'])) {
            unset($commandData['category']);
        }

        if (empty($commandData['subCategory'])) {
            unset($commandData['subCategory']);
        }

        if (empty($commandData['goodsOrPsv'])) {
            unset($commandData['goodsOrPsv']);
        }

        if (empty($commandData['letterIssueType'])) {
            unset($commandData['letterIssueType']);
        } else {
            // Rename to letterIssueTypeId for the DTO
            $commandData['letterIssueTypeId'] = $commandData['letterIssueType'];
            unset($commandData['letterIssueType']);
        }

        // publishFrom field removed from form - unset if present
        if (isset($commandData['publishFrom'])) {
            unset($commandData['publishFrom']);
        }

        // Add escape false for EditorJs content
        if (isset($commandData['defaultBodyContent'])) {
            // Content is already JSON, no additional processing needed
        }

        return $commandData;
    }

    /**
     * Map errors onto the form, any global errors should be returned so they can be added
     * to the flash messenger
     *
     * @param FormInterface $form Form interface
     * @param array $errors array response from errors
     * @return array
     */
    public static function mapFromErrors(FormInterface $form, array $errors): array
    {
        return $errors;
    }
}
