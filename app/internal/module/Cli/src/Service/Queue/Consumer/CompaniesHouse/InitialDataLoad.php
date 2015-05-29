<?php

/**
 * Companies House Initial Data Load Queue Consumer
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Cli\Service\Queue\Consumer\CompaniesHouse;

/**
 * Companies House Initial Data Load Queue Consumer
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class InitialDataLoad extends AbstractConsumer
{
    /**
     * @var string the Business Service class to handle processing
     */
    protected $businessServiceName = 'Cli\CompaniesHouseLoad';
}
