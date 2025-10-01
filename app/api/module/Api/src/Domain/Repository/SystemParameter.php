<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\System\SystemParameter as Entity;

/**
 * System Parameter
 */
class SystemParameter extends AbstractRepository
{
    protected $entity = Entity::class;

    public const DIGITAL_CONTINUATION_REMINDER_PERIOD_DEFAULT = 21;

    /**
     * Fetch a system parameter value, return null if not found
     *
     * @param string $key The system parameter name, see SystemParameter entity constants
     *
     * @return mixed|null
     */
    public function fetchValue($key)
    {
        try {
            return $this->fetchById($key)->getParamValue();
        } catch (NotFoundException) {
            return null;
        }
    }

    /**
     * Get Disable card payment setting
     *
     * @return bool
     */
    public function getDisableSelfServeCardPayments()
    {
        return (bool) $this->fetchValue(Entity::DISABLED_SELFSERVE_CARD_PAYMENTS);
    }

    /**
     * Get Disable Digital Continuations
     *
     * @return bool Return true if disabled
     */
    public function getDisabledDigitalContinuations()
    {
        return (bool) $this->fetchValue(Entity::DISABLE_DIGITAL_CONTINUATIONS);
    }

    /**
     * Get the Digital continuation reminder period (days), if value if not numeric will return a default
     *
     * @return int Period, number of days
     */
    public function getDigitalContinuationReminderPeriod()
    {
        $period = $this->fetchValue(Entity::DIGITAL_CONTINUATION_REMINDER_PERIOD);
        if (!is_numeric($period)) {
            $period = self::DIGITAL_CONTINUATION_REMINDER_PERIOD_DEFAULT;
        }
        return (int)$period;
    }

    /**
     * Get Disable Data Retention Document Delete
     *
     * @return bool Return true if disabled
     */
    public function getDisableDataRetentionDocumentDelete()
    {
        $value = $this->fetchValue(Entity::DISABLE_DATA_RETENTION_DOCUMENT_DELETE);
        // if value is null, ie not set, then its disabled for safety
        if ($value === null) {
            return true;
        }
        return (bool) $value;
    }

    /**
     * Get Disable Data Retention Deletes
     *
     * @return bool Return true if disabled
     */
    public function getDisableDataRetentionDelete()
    {
        $value = $this->fetchValue(Entity::DISABLE_DATA_RETENTION_DELETE);
        // if value is null, ie not set, then its disabled for safety
        if ($value === null) {
            return true;
        }
        return (bool) $value;
    }

    /**
     * Get the user ID of the data retention system user
     *
     * @return int
     * @throws RuntimeException
     */
    public function getSystemDataRetentionUser()
    {
        $value = (int) $this->fetchValue(Entity::SYSTEM_DATA_RETENTION_USER);
        if ($value === 0) {
            throw new RuntimeException(
                'System parameter "' . Entity::SYSTEM_DATA_RETENTION_USER . '" is not set'
            );
        }
        return $value;
    }

    /**
     * Get the data retention delete limit
     *
     * @return int
     */
    public function getDataRetentionDeleteLimit()
    {
        return (int) $this->fetchValue(Entity::DR_DELETE_LIMIT);
    }

    /**
     * Is prompt after login on selfserve enabled
     *
     * @return bool
     */
    public function isSelfservePromptEnabled(): bool
    {
        return (bool) $this->fetchValue(Entity::ENABLE_SELFSERVE_PROMPT);
    }
}
