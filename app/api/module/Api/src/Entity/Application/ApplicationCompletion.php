<?php

namespace Dvsa\Olcs\Api\Entity\Application;

use Doctrine\ORM\Mapping as ORM;
use Laminas\Filter\Word\UnderscoreToCamelCase;

/**
 * ApplicationCompletion Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="application_completion",
 *    indexes={
 *        @ORM\Index(name="ix_application_completion_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_application_completion_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_application_completion_application_id", columns={"application_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_application_completion_application_id", columns={"application_id"})
 *    }
 * )
 */
class ApplicationCompletion extends AbstractApplicationCompletion
{
    public const STATUS_NOT_STARTED = 0;
    public const STATUS_INCOMPLETE = 1;
    public const STATUS_COMPLETE = 2;

    // On a variation the statuses mean difference things
    public const STATUS_VARIATION_REQUIRES_ATTENTION = 1;
    public const STATUS_VARIATION_UPDATED = 2;

    public const SECTION_PEOPLE = 'people';
    public const SECTION_TRANSPORT_MANAGER = 'transport_managers';
    public const SECTION_LICENCE_HISTORY = 'licence_history';
    public const SECTION_FINANCIAL_HISTORY = 'financial_history';
    public const SECTION_DECLARATION_INTERNAL = 'declarations_internal';
    public const SECTION_CONVICTIONS_AND_PENALTIES = 'convictions_penalties';
    // For some reason declaration section is called 'undertakings'?
    public const SECTION_DECLARATION = 'undertakings';

    /**
     * ApplicationCompletion constructor.
     *
     * @param Application $application Application
     *
     * @return void
     */
    public function __construct(Application $application)
    {
        $this->setApplication($application);
    }

    /**
     * Whether the section has been updated (status 2), note that for new apps a
     * status of 2 means something different i.e. completed
     *
     * @param string $section section
     *
     * @return bool
     */
    public function variationSectionUpdated($section)
    {
        $sectionStatus = $section . 'Status';
        return $this->$sectionStatus === self::STATUS_VARIATION_UPDATED;
    }

    /**
     * Get Calculated Values
     *
     * @return array
     * @deprecated
     */
    protected function getCalculatedValues()
    {
        return ['application' => null];
    }

    /**
     * Is Complete
     *
     * @param array $required Sections
     *
     * @return bool
     */
    public function isComplete($required)
    {
        return count($this->getIncompleteSections($required)) < 1;
    }

    /**
     * Get Incomplete sections
     *
     * @param array $required Sections
     *
     * @return array
     */
    public function getIncompleteSections($required)
    {
        $incompleteSections = [];

        $filter = new UnderscoreToCamelCase();

        foreach ($required as $section) {
            $getter = 'get' . ucfirst((string) $filter->filter($section)) . 'Status';
            if ($this->$getter() !== self::STATUS_COMPLETE) {
                $incompleteSections[] = $section;
            }
        }

        return $incompleteSections;
    }
}
