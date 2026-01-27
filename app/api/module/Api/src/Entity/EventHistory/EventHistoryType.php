<?php

namespace Dvsa\Olcs\Api\Entity\EventHistory;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventHistoryType Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="event_history_type")
 */
class EventHistoryType extends AbstractEventHistoryType
{
    public const EVENT_CODE_PASSWORD_RESET = 'PWR';
    public const EVENT_CODE_SURRENDER_UNDER_CONSIDERATION = 'SUC';
    public const EVENT_CODE_SURRENDER_APPLICATION_WITHDRAWN = 'SAW';
    public const IRHP_APPLICATION_CREATED = 'PAC';
    public const IRHP_APPLICATION_UPDATED = 'PAU';
    public const IRHP_APPLICATION_SUBMITTED = 'PAS';
    public const IRHP_APPLICATION_GRANTED = 'PAG';
    public const USER_EMAIL_ADDRESS_UPDATED = 'UEU';
    public const INTERIM_END = 'INE';
    public const EVENT_CODE_CONDITION_CHANGED = 'COG';
    public const EVENT_CODE_UNDERTAKING_CHANGED = 'UTG';
    public const EVENT_CODE_CHANGE_CORRESPONDENCE_ADDRESS = 'CCA';
    public const EVENT_CODE_ADD_SAFETY_INSPECTOR = 'ASI';
    public const EVENT_CODE_EDIT_SAFETY_INSPECTOR = 'ESI';
    public const EVENT_CODE_DELETE_SAFETY_INSPECTOR = 'DSI';
    public const EVENT_CODE_EDIT_OPERATING_CENTRE = 'COC';
}
