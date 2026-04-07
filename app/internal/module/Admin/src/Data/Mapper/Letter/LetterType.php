<?php

declare(strict_types=1);

namespace Admin\Data\Mapper\Letter;

use Common\Data\Mapper\MapperInterface;
use Laminas\Form\FormInterface;

class LetterType implements MapperInterface
{
    /**
     * Map data from a result array into an array suitable for a form
     *
     * @param array $data Data from query
     * @return array
     */
    #[\Override]
    public static function mapFromResult(array $data): array
    {
        // Extract section IDs and required flags from letterTypeSections collection (ordered by displayOrder)
        $sectionIds = [];
        $requiredSectionIds = [];
        if (!empty($data['letterTypeSections'])) {
            // Sort by displayOrder to preserve assembly ordering
            $sections = $data['letterTypeSections'];
            usort($sections, fn($a, $b) => ($a['displayOrder'] ?? 0) <=> ($b['displayOrder'] ?? 0));
            foreach ($sections as $lts) {
                $sectionId = $lts['letterSectionVersion']['letterSection']['id']
                    ?? $lts['letterSection']['id']
                    ?? null;
                if ($sectionId) {
                    $sectionIds[] = $sectionId;
                    if (!empty($lts['isRequired'])) {
                        $requiredSectionIds[] = $sectionId;
                    }
                }
            }
        }

        // Extract appendix IDs from letterTypeAppendices collection
        $appendixIds = [];
        if (!empty($data['letterTypeAppendices'])) {
            foreach ($data['letterTypeAppendices'] as $lta) {
                if (isset($lta['letterAppendixVersion']['letterAppendix']['id'])) {
                    $appendixIds[] = $lta['letterAppendixVersion']['letterAppendix']['id'];
                }
            }
        }

        // Extract choice IDs from letterTypeChoices collection
        $choiceIds = [];
        if (!empty($data['letterTypeChoices'])) {
            foreach ($data['letterTypeChoices'] as $ltc) {
                $choiceId = $ltc['letterChoice']['id'] ?? $ltc['letterChoiceId'] ?? null;
                if ($choiceId) {
                    $choiceIds[] = $choiceId;
                }
            }
        }

        $formData = [
            'letterType' => [
                'id' => $data['id'] ?? null,
                'name' => $data['name'] ?? null,
                'description' => $data['description'] ?? null,
                'isActive' => $data['isActive'] ?? true,
                'masterTemplate' => isset($data['masterTemplate']['id']) ? $data['masterTemplate']['id'] : null,
                'category' => isset($data['category']['id']) ? $data['category']['id'] : null,
                'subCategory' => isset($data['subCategory']['id']) ? $data['subCategory']['id'] : null,
                'letterTestData' => isset($data['letterTestData']['id']) ? $data['letterTestData']['id'] : null,
                'sections' => $sectionIds,
                'sectionsOrder' => implode(',', $sectionIds),
                'sectionsRequired' => implode(',', $requiredSectionIds),
                'choices' => $choiceIds,
                'appendices' => $appendixIds,
            ]
        ];

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
        $commandData = $data['letterType'] ?? [];

        // Remove the id field if it's empty (for create operations)
        if (empty($commandData['id'])) {
            unset($commandData['id']);
        }

        // Ensure boolean value for isActive
        if (isset($commandData['isActive'])) {
            $commandData['isActive'] = (bool) $commandData['isActive'];
        }

        // Handle empty selections
        if (empty($commandData['masterTemplate'])) {
            unset($commandData['masterTemplate']);
        }

        if (empty($commandData['category'])) {
            unset($commandData['category']);
        }

        if (empty($commandData['subCategory'])) {
            unset($commandData['subCategory']);
        }

        if (empty($commandData['letterTestData'])) {
            unset($commandData['letterTestData']);
        }

        // Use sectionsOrder (from hidden input) for ordered section IDs, fall back to sections multi-select
        $sectionsOrder = $commandData['sectionsOrder'] ?? '';
        if (!empty($sectionsOrder)) {
            $commandData['sections'] = array_filter(explode(',', $sectionsOrder));
        } else {
            $commandData['sections'] = array_filter((array)($commandData['sections'] ?? []));
        }
        unset($commandData['sectionsOrder']);

        // Parse required sections (comma-separated IDs from hidden input)
        $sectionsRequired = $commandData['sectionsRequired'] ?? '';
        $commandData['sectionsRequired'] = !empty($sectionsRequired)
            ? array_filter(explode(',', $sectionsRequired))
            : [];


        // Ensure appendices and choices are always arrays so the handler processes removals.
        $commandData['appendices'] = array_filter((array)($commandData['appendices'] ?? []));
        $commandData['choices'] = array_filter((array)($commandData['choices'] ?? []));

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
