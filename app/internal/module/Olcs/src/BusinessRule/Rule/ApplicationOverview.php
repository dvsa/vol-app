<?php

/**
 * Application Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Common\BusinessRule\Rule;

use Common\BusinessRule\BusinessRuleInterface;

/**
 * Application Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationOverview implements BusinessRuleInterface
{
    public function filter($data)
    {
        $filtered = [
            'id' => $data['id'],
            'version' => $data['version'],
            'receivedDate' => $data['receivedDate'],
            'targetCompletionDate' => $data['targetCompletionDate'],
        ];

        return $filtered;
    }
}
