<?php

/**
 * EventProcessor Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Service\Translator;

/**
 * EventProcessor Interface
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
interface EventProcessor
{
    /**
     * Process an event
     *
     * @param object $e
     */
    public static function processEvent($e);
}
