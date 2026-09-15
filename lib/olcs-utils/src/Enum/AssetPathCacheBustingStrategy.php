<?php

namespace Dvsa\Olcs\Utils\Enum;

enum AssetPathCacheBustingStrategy: string
{
    case None = 'none';
    case Release = 'release';
    case UnixTimestamp = 'timestamp';
}
