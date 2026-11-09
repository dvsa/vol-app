#!/usr/bin/env bash
#
# Checks that one part of the local Docker environment (compose.yaml) works, retrying while it
# starts. .github/workflows/localdev.yaml runs one check per step. To check a local stack:
#
#   .github/scripts/localdev-check.sh <check>
#
# Run it from the repository root, with the stack started by `docker compose up`. The checks
# need no database data and no app setup (npm run refresh), so the apps themselves are only
# checked as far as PHP answering a request.

set -euo pipefail

# How long a check keeps retrying, in seconds. Set TIMEOUT to change it; otherwise each check
# uses its own default.
TIMEOUT="${TIMEOUT:-}"

# The host names that nginx-proxy routes, from the services' VIRTUAL_HOST settings.
DOMAIN="local.olcs.dev-dvsacloud.uk"

# A print job title of this run's own, so a PDF left by an earlier run can't pass the check.
PRINT_TITLE="localdev-check-$$"

in_service() {
  docker compose exec -T "$@"
}

# Runs a command until it succeeds, for up to $TIMEOUT seconds (300 unless set).
retry() {
  local timeout="${TIMEOUT:-300}" deadline
  deadline=$((SECONDS + timeout))
  until "$@"; do
    if ((SECONDS >= deadline)); then
      echo "Still failing after ${timeout}s: $*" >&2
      return 1
    fi
    sleep 5
  done
}

# Requests a path through nginx-proxy, as a browser on the developer's machine would, and
# prints the HTTP status.
status_through_proxy() {
  curl -s -o /dev/null -w '%{http_code}' -H "Host: $1.$DOMAIN" "http://localhost$2"
}

returns_200_through_proxy() {
  [[ "$(status_through_proxy "$1" "$2")" == 200 ]]
}

mysql_answers() {
  in_service -e MYSQL_PWD=olcs db mysql -h 127.0.0.1 -u root -e 'SELECT 1' olcs_be >/dev/null 2>&1
}

valkey_answers() {
  [[ "$(in_service redis valkey-cli ping)" == PONG ]]
}

# The same tools, port and directory as the refresh step's LDAP reset (ResetLdap.ts).
ldap_answers() {
  in_service openldap /bin/bash -c \
    'ldapsearch -D "cn=admin,dc=vol,dc=dvsa" -H ldap://localhost:1389 -w admin -LLL -s base -b "dc=vol,dc=dvsa" dn' |
    grep -q '^dn: dc=vol,dc=dvsa'
}

# The api container sends mail to Mailpit over SMTP (mail.options in local.php.dist).
mailpit_accepts_smtp_from_api() {
  # shellcheck disable=SC2016 # the $ names are PHP variables, not shell ones
  in_service api php -r '
    $smtp = @fsockopen("mailpit", 1025, $code, $message, 5);
    exit($smtp && str_starts_with((string) fgets($smtp), "220") ? 0 : 1);
  '
}

gotenberg_is_healthy() {
  in_service api curl -sf http://pdf-converter:3000/health >/dev/null
}

# Converts an RTF document to PDF from the api container, through LibreOffice, as the app does.
gotenberg_converts_rtf() {
  printf '%s' '{\rtf1\ansi{\fonttbl{\f0 Arial;}}\f0 Local development environment check\par}' |
    in_service api sh -c '
      curl -sf -F "files=@-;filename=check.rtf" http://pdf-converter:3000/forms/libreoffice/convert -o /tmp/check.pdf &&
      head -c 4 /tmp/check.pdf | grep -q "%PDF"
    '
}

printer_is_ready() {
  in_service printpit lpstat -p PrintPit | grep -qE 'idle|printing'
}

# Prints from the cli container with lpr, as the app's print job does (PrintJob.php).
print_from_cli() {
  printf 'Local development environment check\n' |
    in_service cli lpr -H printpit:631 -P PrintPit -T "$PRINT_TITLE"
}

printed_pdf_exists() {
  in_service printpit sh -c "ls /var/spool/cups-pdf/*$PRINT_TITLE*.pdf" >/dev/null 2>&1
}

# FileBrowser's API needs a token, which POST /api/login gives without credentials under --noauth.
filebrowser_lists_printed_pdf() {
  local token
  token="$(curl -sf -X POST -H "Host: printpit.$DOMAIN" http://localhost/api/login)"
  curl -sf -H "Host: printpit.$DOMAIN" -H "X-Auth: $token" http://localhost/api/resources/ |
    grep -q "$PRINT_TITLE"
}

# Checks for the PHP extensions that the Dockerfile's development stage adds.
php_has_extensions() {
  local service="$1" modules extension
  shift
  modules="$(in_service "$service" php -m)"
  for extension in "$@"; do
    if ! grep -qx "$extension" <<<"$modules"; then
      echo "$service is missing the PHP extension $extension" >&2
      return 1
    fi
  done
}

# Without npm run refresh the apps have no vendor directory or config, so PHP answers with an
# error (500), and any answer from PHP counts. A stopped container gets 503 from nginx-proxy,
# and a container whose PHP-FPM has died gets 502 from its own nginx.
app_answers_through_proxy() {
  local status
  status="$(status_through_proxy "$1" /)"
  echo "$1.$DOMAIN answered $status"
  [[ "$status" != 000 && "$status" != 502 && "$status" != 503 && "$status" != 504 ]]
}

check="${1:-}"
case "$check" in
  db)
    retry mysql_answers
    ;;
  redis)
    retry valkey_answers
    ;;
  openldap)
    retry ldap_answers
    ;;
  mailpit)
    retry mailpit_accepts_smtp_from_api
    retry returns_200_through_proxy mailpit /
    ;;
  pdf-converter)
    retry gotenberg_is_healthy
    retry gotenberg_converts_rtf
    ;;
  printpit)
    retry printer_is_ready
    print_from_cli
    retry printed_pdf_exists
    retry filebrowser_lists_printed_pdf
    ;;
  cdn)
    # The cdn container runs npm install and builds the assets before it serves them.
    TIMEOUT="${TIMEOUT:-600}" retry returns_200_through_proxy cdn /styles/selfserve.css
    ;;
  api)
    php_has_extensions api xdebug ldap
    retry app_answers_through_proxy api
    ;;
  selfserve)
    php_has_extensions selfserve xdebug
    retry app_answers_through_proxy ssweb
    ;;
  internal)
    php_has_extensions internal xdebug
    retry app_answers_through_proxy iuweb
    ;;
  cli)
    php_has_extensions cli xdebug ldap
    ;;
  *)
    echo "Usage: $0 db|redis|openldap|mailpit|pdf-converter|printpit|cdn|api|selfserve|internal|cli" >&2
    exit 2
    ;;
esac

echo "$check: OK"
