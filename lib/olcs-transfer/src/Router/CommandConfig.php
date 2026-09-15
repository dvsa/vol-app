<?php

namespace Dvsa\Olcs\Transfer\Router;

/**
 * Command Config
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CommandConfig
{
    public static function getDeleteConfig($dto)
    {
        return self::getConfig('DELETE', $dto);
    }

    public static function getPutConfig($dto)
    {
        return self::getConfig('PUT', $dto);
    }

    public static function getPostConfig($dto)
    {
        return self::getConfig('POST', $dto);
    }

    private static function getConfig($method, $dto)
    {
        return [
            'type' => \Laminas\Router\Http\Method::class,
            'options' => [
                'verb' => $method,
                'defaults' => [
                    'dto' => $dto
                ]
            ]
        ];
    }
}
