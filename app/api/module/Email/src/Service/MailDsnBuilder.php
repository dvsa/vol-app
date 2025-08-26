<?php

namespace Dvsa\Olcs\Email\Service;

final class MailDsnBuilder
{
    public static function buildFromConfig(array $cfg): string
    {
        $opt   = $cfg['options'] ?? [];
        $host  = $opt['host'] ?? 'localhost';
        $port  = $opt['port'] ?? null;

        $connClass = strtolower((string)($opt['connection_class'] ?? ''));
        $connCfg   = $opt['connection_config'] ?? [];

        $user = $connCfg['username'] ?? null;
        $pass = $connCfg['password'] ?? null;

        $ssl  = $connCfg['ssl'] ?? null;
        $enc  = in_array($ssl, ['tls','ssl'], true) ? $ssl : null;

        $authMode = null;
        if (in_array($connClass, ['plain','login','crammd5','cram-md5'], true)) {
            $authMode = $connClass === 'crammd5' ? 'cram-md5' : $connClass;
        }

        $auth = '';
        if ($user) {
            $auth = rawurlencode($user) . ($pass !== null ? ':' . rawurlencode($pass) : '') . '@';
        }

        $hostPart = (str_contains($host, ':') && $host[0] !== '[') ? '['.$host.']' : $host;
        $portPart = $port ? ':' . (int)$port : '';

        $q = [];
        if ($enc)      { $q['encryption'] = $enc; }
        if ($authMode) { $q['auth_mode']  = $authMode; }
        if (isset($connCfg['timeout'])) { $q['timeout'] = (int)$connCfg['timeout']; }

        $query = $q ? '?' . http_build_query($q, '', '&', PHP_QUERY_RFC3986) : '';
        return sprintf('smtp://%s%s%s%s', $auth, $hostPart, $portPart, $query);
    }
}
