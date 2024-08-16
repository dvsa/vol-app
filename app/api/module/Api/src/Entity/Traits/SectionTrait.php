<?php

namespace Dvsa\Olcs\Api\Entity\Traits;

use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\SectionableInterface;

/**
 * Section Trait
 */
trait SectionTrait
{
    /**
     * @var array
     */
    private $sectionCompletion;

    /**
     * Is the field agreed
     *
     * @param string $field field being checked
     *
     * @return bool
     */
    private function fieldIsAgreed($field)
    {
        return $this->$field === true;
    }

    /**
     * Is the field not null
     *
     * @param string $field field being checked
     *
     * @return bool
     */
    private function fieldIsNotNull($field)
    {
        return $this->$field !== null;
    }

    /**
     * Checks an array collection has records
     *
     * @param string $field field being checked
     *
     * @return bool
     */
    private function collectionHasRecord($field)
    {
        return !$this->$field->isEmpty();
    }

    /**
     * Get the section completion
     *
     * @return array
     * @throws RuntimeException
     */
    public function getSectionCompletion()
    {
        if (!isset($this->sectionCompletion)) {
            $this->populateSectionCompletion();
        }
        return $this->sectionCompletion;
    }

    /**
     * Get the sections
     *
     * @return array
     * @throws RuntimeException
     */
    private function getSections()
    {
        if (!defined('static::SECTIONS')) {
            throw new RuntimeException('Missing required definition of sections');
        }

        $irhpPermitTypeId = $this->getIrhpPermitType()->getId();

        if (!isset(static::SECTIONS[$irhpPermitTypeId]) || !is_array(static::SECTIONS[$irhpPermitTypeId])) {
            throw new RuntimeException(
                'Missing required definition of sections for irhpPermitTypeId: ' . $irhpPermitTypeId
            );
        }

        return static::SECTIONS[$irhpPermitTypeId];
    }

    /**
     * Populate the section completion
     *
     * @return void
     * @throws RuntimeException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function populateSectionCompletion()
    {
        $this->sectionCompletion = [];

        if ($this->isApplicationPathEnabled()) {
            // q&a
            $data = $this->getQuestionAnswerData();

            foreach ($data as $value) {
                // build the section completion array is the currently used format of 'field name' => 'status'
                // canCheckAnswers() / canMakeDeclaration() / canBeSubmitted() need it
                // to be refactored once all permit types are migrated to q&a
                $this->sectionCompletion[$value['section']] = $value['status'];
                $totalSections = count($this->sectionCompletion);
            }
        } else {
            // backward compatibility
            $sections = $this->getSections();
            $totalSections = count($sections);

            foreach ($sections as $field => $section) {
                // validate the field
                $this->validateField($field);
            }
        }

        // populate the summary
        $countByStatus = array_count_values($this->sectionCompletion);
        $totalCompleted = $countByStatus[SectionableInterface::SECTION_COMPLETION_COMPLETED] ?? 0;

        $this->sectionCompletion['totalSections'] = $totalSections;
        $this->sectionCompletion['totalCompleted'] = $totalCompleted;
        $this->sectionCompletion['allCompleted'] = ($totalSections === $totalCompleted);
    }

    /**
     * Resets section completion on demand
     */
    public function resetSectionCompletion()
    {
        $this->populateSectionCompletion();
    }

    /**
     * Validate the field
     *
     * @param string $field field being validated
     *
     * @return void
     */
    private function validateField($field)
    {
        if (isset($this->sectionCompletion[$field])) {
            // already validated
            return;
        }

        $section = $this->getSections()[$field];

        if (isset($section['validateIf']) && is_array($section['validateIf'])) {
            foreach ($section['validateIf'] as $f => $v) {
                // validate the dependency first
                $this->validateField($f);

                // check if value of the dependency field is what is expected
                if ($this->sectionCompletion[$f] !== $v) {
                    // the first value different to what is expected sets the field to cannot start
                    $this->sectionCompletion[$field] = SectionableInterface::SECTION_COMPLETION_CANNOT_START;
                    return;
                }
            }
        }

        // validate the field itself
        $validator = $section['validator'];

        if ($validator == SectionableInterface::VALIDATOR_ALWAYS_TRUE) {
            $validatorResponse = true;
        } else {
            $validatorResponse = $this->$validator($field);
        }

        $this->sectionCompletion[$field] = $validatorResponse
            ? SectionableInterface::SECTION_COMPLETION_COMPLETED
            : SectionableInterface::SECTION_COMPLETION_NOT_STARTED;
    }

    /**
     * Checks if particular field validates
     *
     * @param string $field field being checked
     *
     * @return bool
     */
    protected function isFieldReadyToComplete($field)
    {
        // canCheckAnswers() / canMakeDeclaration() / canBeSubmitted() uses this method atm
        if ($this->getIrhpPermitType()->isApplicationPathEnabled()) {
            // q&a
            $this->getSectionCompletion();
        } else {
            // backward compatibility
            $this->validateField($field);
        }

        return $this->sectionCompletion[$field] !== SectionableInterface::SECTION_COMPLETION_CANNOT_START;
    }
}
