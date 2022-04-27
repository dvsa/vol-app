<?php
declare(strict_types = 1);

namespace Olcs\View\Helper\SessionTimeoutWarning;

use Laminas\Filter\ToInt;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\Digits;
use Laminas\Validator\InArray;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\Uri;

class SessionTimeoutWarningFactoryConfigInputFilter extends InputFilter
{
    const CONFIG_ENABLED = 'enabled';
    const CONFIG_SECONDS_BEFORE_EXPIRY_WARNING = 'seconds-before-expiry-warning';
    const CONFIG_TIMEOUT_REDIRECT_URL = 'timeout-redirect-url';

    /**
     * @var Input
     */
    private $enabled;

    /**
     * @var Input
     */
    private $secondsBeforeExpiryWarning;

    /**
     * @var Input
     */
    private $timeoutRedirectUrl;

    /**
     * SessionTimeoutWarningFactoryConfigInputFilter constructor.
     */
    public function __construct()
    {
        $this->enabled = new Input(self::CONFIG_ENABLED);

        $inArray = new InArray();
        $inArray->setHaystack(['1', '0', true, false])
            ->setStrict(InArray::COMPARE_STRICT);

        $this->enabled
            ->getValidatorChain()
            ->attach(new NotEmpty)
            ->attach($inArray);

        $this->add($this->enabled);

        $this->secondsBeforeExpiryWarning = new Input(self::CONFIG_SECONDS_BEFORE_EXPIRY_WARNING);
        $this->secondsBeforeExpiryWarning
            ->getValidatorChain()
            ->attach(new NotEmpty)
            ->attach(new Digits);
        $this->secondsBeforeExpiryWarning
            ->getFilterChain()
            ->attach(new ToInt);

        $this->add($this->secondsBeforeExpiryWarning);

        $this->timeoutRedirectUrl = new Input(self::CONFIG_TIMEOUT_REDIRECT_URL);
        $this->timeoutRedirectUrl
            ->getValidatorChain()
            ->attach(new NotEmpty)
            ->attach(new Uri);

        $this->add($this->timeoutRedirectUrl);
    }
}
