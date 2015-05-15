<?php

/**
 * Companies House Compare Queue Consumer
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Cli\Service\Queue\Consumer\CompaniesHouse;

/**
 * Companies House Compare Queue Consumer
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class Compare extends AbstractConsumer
{
    /**
     * @var string the Business Service class to handle processing
     */
    protected $businessServiceName = 'Cli\CompaniesHouseCompare';
}
