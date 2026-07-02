<?php

namespace Dvsa\Olcs\Transfer\Validators;

use Exception;
use Laminas\Validator\AbstractValidator as AbstractValidator;

/**
 * Checks if a date is in the future
 *
 * @author Alex Peshkov <alex.peshkov@valtechg.co.uk>
 */
class DateInFuture extends AbstractValidator
{
    /**
     * @const string
     */
    public const IN_FUTURE = 'inFuture';

    /**
     * @const string
     */
    public const TODAY_OR_IN_FUTURE = 'todayOrInFuture';

    /**
     * @var bool
     */
    protected $includeToday;

    /**
     * @var bool
     */
    protected $useTime;

    /**
     * @var bool
     */
    protected $allowEmpty;

    /**
     * Set includeToday option
     *
     * @param bool $includeToday include today
     *
     * @return $this
     */
    public function setIncludeToday($includeToday)
    {
        $this->includeToday = (bool) $includeToday;
        return $this;
    }

    /**
     * Get includeToday option
     *
     * @return bool
     */
    public function getIncludeToday()
    {
        return $this->includeToday;
    }

    /**
     * Set useTime option
     *
     * @param bool $useTime use time
     *
     * @return $this
     */
    public function setUseTime($useTime)
    {
        $this->useTime = (bool) $useTime;
        return $this;
    }

    /**
     * Get useTime option
     *
     * @return bool
     */
    public function getUseTime()
    {
        return $this->useTime;
    }

    /**
     * Set allowEmpty option
     *
     * @param bool $allowEmpty use time
     *
     * @return $this
     */
    public function setAllowEmpty($allowEmpty)
    {
        $this->allowEmpty = (bool) $allowEmpty;
        return $this;
    }

    /**
     * Get allowEmpty option
     *
     * @return bool
     */
    public function getAllowEmpty()
    {
        return $this->allowEmpty;
    }

    /**
     * Sets options
     *
     * @param mixed $options options
     *
     * @return AbstractValidator
     */
    #[\Override]
    public function setOptions($options = []): AbstractValidator
    {
        if (isset($options['include_today'])) {
            $this->setIncludeToday($options['include_today']);
        }
        if (isset($options['use_time'])) {
            $this->setUseTime($options['use_time']);
        }
        if (isset($options['allow_empty'])) {
            $this->setAllowEmpty($options['allow_empty']);
        }

        return parent::setOptions($options);
    }

    /**
     * Error messages
     * @var array
     */
    protected $messageTemplates = [
        self::IN_FUTURE => 'This date should be in the future',
        self::TODAY_OR_IN_FUTURE => 'This date should be today or in the future'
    ];

    /**
     * Returns true if the date is not in the future
     *
     * @param mixed $value value
     *
     * @return bool
     * @throws Exception if the token doesn't exist in the context array
     */
    #[\Override]
    public function isValid($value)
    {
        if (empty($value) && $this->getAllowEmpty()) {
            return true;
        } elseif (empty($value)) {
            return false;
        }

        $date = new \DateTime($value);
        $today = $this->getNowDateTime();

        if ($this->getIncludeToday()) {
            if ($date < $today) {
                $this->error(self::TODAY_OR_IN_FUTURE);
                return false;
            }
        } else {
            if ($date <= $today) {
                $this->error(self::IN_FUTURE);
                return false;
            }
        }
        return true;
    }

    /**
     * Get Now
     *
     * @return \DateTime
     */
    protected function getNowDateTime()
    {
        $today = new \DateTime();
        if (!$this->getUseTime()) {
            $today->setTime(0, 0, 0);
        }
        return $today;
    }
}
