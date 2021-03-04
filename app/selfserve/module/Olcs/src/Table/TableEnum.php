<?php

namespace Olcs\Table;

use Common\Enum\DefinesEnumerations;

class TableEnum implements DefinesEnumerations
{
    const BUS_REG_BROWSE = 'bus-reg-browse';
    const BUS_REG_VARIATION_HISTORY = 'bus-reg-variation-history';
    const BUS_REG_REGISTRATIONS = 'busreg-registrations';
    const CORRESPONDENCE = 'correspondence';
    const DASHBOARD_ASPPLICATIONS = 'dashboard-applications';
    const DASHBOARD_LICENCES = 'dashboard-licences';
    const DASHBOARD_TM_APPLICATIONS = 'dashboard-tm-applications';
    const DASHBOARD_VARIATIONS = 'dashboard-variations';
    const EBSR_SUBMISSIONS = 'ebsr-submissions';
    const ENTITY_VIEW_CONDITIONS_UNDERTAKINGS_PARTNER = 'entity-view-conditions-undertakings-partner';
    const ENTITY_VIEW_CURRENT_APPLICATIONS_PARTNER = 'entity-view-current-applications-partner';
    const ENTITY_VIEW_OPERATING_CENTERS_ANONYMOUS = 'entity-view-operating-centres-anonymous';
    const ENTITY_VIEW_OPERATING_CENTERS_PARTNER = 'entity-view-operating-centres-partner';
    const ENTITY_VIEW_OPPOSITIONS_ANONYMOUS = 'entity-view-oppositions-anonymous';
    const ENTITY_VIEW_OPPOSITIONS_PARTNER = 'entity-view-oppositions-partner';
    const ENTITY_VIEW_RELATED_OPERATOR_LICENCES = 'entity-view-related-operator-licences';
    const ENTITY_VIEW_TRANSPORT_PARTNER = 'entity-view-transport-managers';
    const ENTITY_VIEW_VEHICLES_PARTNER = 'entity-view-vehicles-partner';
    const FEES = 'fees';
    const LICENCE_VEHICLE_LIST_CURRENT = 'licence-vehicle-list-current';
    const LICENCE_VEHICLE_LIST_EXPORT_CURRENT_AND_REMOVED = 'licence-vehicle-list-export-current-and-removed';
    const LICENCE_VEHICLE_LIST_REMOVED = 'licence-vehicle-list-removed';
    const LICENCE_VEHICLES = 'licence-vehicles';
    const LVA_LICENCE_OPERATING_CENTERS = 'lva-licence-operating-centres';
    const TXC_INBOX = 'txc-inbox';
    const USERS = 'users';

    /**
     * @inheritDoc
     */
    public function getEnumerations(): array
    {
        return [
            static::BUS_REG_BROWSE,
            static::BUS_REG_VARIATION_HISTORY,
            static::BUS_REG_REGISTRATIONS,
            static::CORRESPONDENCE,
            static::DASHBOARD_ASPPLICATIONS,
            static::DASHBOARD_LICENCES,
            static::DASHBOARD_TM_APPLICATIONS,
            static::DASHBOARD_VARIATIONS,
            static::EBSR_SUBMISSIONS,
            static::ENTITY_VIEW_CONDITIONS_UNDERTAKINGS_PARTNER,
            static::ENTITY_VIEW_CURRENT_APPLICATIONS_PARTNER,
            static::ENTITY_VIEW_OPERATING_CENTERS_ANONYMOUS,
            static::ENTITY_VIEW_OPERATING_CENTERS_PARTNER,
            static::ENTITY_VIEW_OPPOSITIONS_ANONYMOUS,
            static::ENTITY_VIEW_OPPOSITIONS_PARTNER,
            static::ENTITY_VIEW_RELATED_OPERATOR_LICENCES,
            static::ENTITY_VIEW_TRANSPORT_PARTNER,
            static::ENTITY_VIEW_VEHICLES_PARTNER,
            static::FEES,
            static::LICENCE_VEHICLE_LIST_CURRENT,
            static::LICENCE_VEHICLE_LIST_EXPORT_CURRENT_AND_REMOVED,
            static::LICENCE_VEHICLE_LIST_REMOVED,
            static::LICENCE_VEHICLES,
            static::LVA_LICENCE_OPERATING_CENTERS,
            static::TXC_INBOX,
            static::USERS,
        ];
    }
}
