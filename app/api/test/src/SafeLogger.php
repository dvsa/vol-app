<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest;

use Laminas\Log\Logger;

/**
 * Logger subclass that guards against null $writers in __destruct().
 *
 * Laminas\Log\Logger::__destruct() iterates $this->writers, which can be null
 * during PHP garbage collection if the SplPriorityQueue is collected before
 * the Logger. PHPUnit 12 surfaces this as a PHP warning.
 */
class SafeLogger extends Logger
{
    public function __destruct()
    {
        if ($this->writers !== null) {
            parent::__destruct();
        }
    }
}
