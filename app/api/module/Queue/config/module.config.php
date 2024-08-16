<?php

use Dvsa\Olcs\Queue\Factories\MessageBuilderFactory;
use Dvsa\Olcs\Queue\Factories\QueueFactory;
use Dvsa\Olcs\Queue\Service\Message\MessageBuilder;
use Dvsa\Olcs\Queue\Service\Queue;

return [
    'service_manager' => [
        'factories' => [
            Queue::class => QueueFactory::class,
            MessageBuilder::class => MessageBuilderFactory::class
        ],
    ]
];
