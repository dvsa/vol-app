<?php
declare(strict_types=1);

$allowedReferer = 'signin.service.gov.uk';

$referer = $_SERVER['HTTP_REFERER'];
if (empty($referer)) {
    header('Location: /', true, 302);
    exit;
}

$refererHost =  parse_url($referer,PHP_URL_HOST);
$trimmedHost = implode('.', array_slice(explode('.', $refererHost), -4, 4));

if ($allowedReferer !== $trimmedHost) {
    header('Location: /', true, 302);
    exit;
}

$samlResponse = $_POST['SAMLResponse'] ?? '';
header("Location: /verify/process-response?SAMLResponse=" . urlencode($samlResponse), true, 302);
exit();

