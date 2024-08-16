<?php

namespace Dvsa\Olcs\Api\Entity;

use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;

/**
 * Sectionable Interface
 */
interface SectionableInterface
{
    public const SECTION_COMPLETION_CANNOT_START = 'section_sts_csy';
    public const SECTION_COMPLETION_NOT_STARTED = 'section_sts_nys';
    public const SECTION_COMPLETION_INCOMPLETE = 'section_sts_inc';
    public const SECTION_COMPLETION_COMPLETED = 'section_sts_com';

    public const VALIDATOR_ALWAYS_TRUE = 'validator_always_true';

    /**
     * Get the section completion
     *
     * @return array
     * @throws RuntimeException
     */
    public function getSectionCompletion();
}
