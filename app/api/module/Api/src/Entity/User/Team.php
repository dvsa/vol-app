<?php

namespace Dvsa\Olcs\Api\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\PrintScan\TeamPrinter;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;

/**
 * Team Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="team",
 *    indexes={
 *        @ORM\Index(name="ix_team_traffic_area_id", columns={"traffic_area_id"}),
 *        @ORM\Index(name="ix_team_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_team_created_by", columns={"created_by"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_team_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class Team extends AbstractTeam
{
    public const ERROR_TEAM_EXISTS = 'err_team_exists';
    public const ERROR_TEAM_LINKED_TO_USERS = 'err_team_linked_to_users';
    public const ERROR_TEAM_LINKED_TO_PRINTER_SETTINGS = 'err_team_linked_to_printer_settings';
    public const ERROR_TEAM_LINKED_TO_TASK_ALLOCATION_RULES = 'err_team_linked_to_task_allocation_rules';
    public const IRFO_TEAM_IDS = [1004];
    public const ALL_ELASTICSEARCH_INDEXES = [
        'licence'     => 'licence',
        'application' => 'search-result-section-applications',
        'case'        => 'internal-navigation-operator-cases',
        'psv_disc'    => 'search.index.psv_disc',
        'vehicle'     => 'search.vehicle-external',
        'address'     => 'search-result-label-address',
        'bus_reg'     => 'search.bus',
        'people'      => 'internal-navigation-operator-people',
        'user'        => 'internal-navigation-operator-users',
        'publication' => 'search.result.publication',
        'irfo'        => 'internal-navigation-operator-irfo'
    ];
    public const NI_SEARCH_INDEXES = [
        'licence'     => 'licence',
        'application' => 'search-result-section-applications',
        'case'        => 'internal-navigation-operator-cases',
        'vehicle'     => 'search.vehicle-external',
        'address'     => 'search-result-label-address',
        'people'      => 'internal-navigation-operator-people',
        'user'        => 'internal-navigation-operator-users',
        'publication' => 'search.result.publication',
    ];

    public function getDefaultTeamPrinter()
    {
        $printers = $this->getTeamPrinters();
        $defaultTeamPrinter = $printers->filter(
            fn($pr) => !$pr->getUser() && !$pr->getSubCategory()
        )->first(); // should be only 1 default printer
        return $defaultTeamPrinter ?: null;
    }

    public function updateDefaultPrinter($newDefaultPrinter)
    {
        $currentDefaultTeamPrinter = $this->getDefaultTeamPrinter();
        if ($currentDefaultTeamPrinter) {
            $currentDefaultTeamPrinter->setPrinter($newDefaultPrinter);
        } else {
            $teamPrinter = new TeamPrinter($this, $newDefaultPrinter);
            $this->addTeamPrinters($teamPrinter);
        }
    }

    /**
     * Work out the traffic areas that the team is allowed to access - some teams are excluded based on a system param
     * (works off param DATA_SEPARATION_TEAMS_EXEMPT)
     *
     *
     * @return array
     */
    public function canAccessAllData(array $excludedTeams = []): bool
    {
        return $this->canAccessGbData($excludedTeams) && $this->canAccessNiData($excludedTeams);
    }

    /**
     * Work out the traffic areas that the team is allowed to access - some teams are excluded based on a system param
     * (works off param DATA_SEPARATION_TEAMS_EXEMPT)
     *
     *
     * @return array
     */
    public function canAccessGbData(array $excludedTeams = []): bool
    {
        if (in_array($this->id, $excludedTeams)) {
            return true;
        }

        return !$this->trafficArea->getIsNi();
    }

    /**
     * Work out the traffic areas that the team is allowed to access - some teams are excluded based on a system param
     * (works off param DATA_SEPARATION_TEAMS_EXEMPT)
     *
     *
     * @return array
     */
    public function canAccessNiData(array $excludedTeams = []): bool
    {
        if (in_array($this->id, $excludedTeams)) {
            return true;
        }

        return $this->trafficArea->getIsNi();
    }

    /**
     * Work out the traffic areas that the team is allowed to access - some teams are excluded based on a system param
     * (works off param DATA_SEPARATION_TEAMS_EXEMPT)
     *
     *
     * @throws \Exception
     * @return array
     */
    public function getAllowedTrafficAreas(array $excludedTeams = []): array
    {
        if ($this->canAccessAllData($excludedTeams)) {
            return array_merge(TrafficArea::GB_TA_IDS, TrafficArea::NI_TA_IDS);
        }

        if ($this->canAccessGbData($excludedTeams)) {
            return TrafficArea::GB_TA_IDS;
        }

        if ($this->canAccessNiData($excludedTeams)) {
            return TrafficArea::NI_TA_IDS;
        }

        return [];
    }

    /**
    * is the team allowed to access IRFO related menus/records
    *
    * @return bool
    */
    public function getIsIrfo(array $excludedTeams = []): bool
    {
        if (in_array($this->id, $excludedTeams)) {
            return true;
        }

        return in_array($this->id, self::IRFO_TEAM_IDS);
    }

    /**
     * Return the elasticsearch indexes internal users may access.
     *
     *
     * @throws \Exception
     * @return array
     */
    public function getAllowedSearchIndexes(array $excludedTeams = []): array
    {
        if ($this->canAccessAllData($excludedTeams) || $this->getIsIrfo($excludedTeams)) {
            return self::ALL_ELASTICSEARCH_INDEXES;
        }

        if ($this->canAccessNiData($excludedTeams)) {
            return self::NI_SEARCH_INDEXES;
        }

        return array_diff_key(self::ALL_ELASTICSEARCH_INDEXES, ['irfo' => 1]);
    }
}
