#!/bin/bash
set -euo pipefail
source_root="$(pwd)"
test_root="$(mktemp -d /tmp/driveed-startup-test.XXXXXX)"
cleanup() {
    case "$test_root" in /tmp/driveed-startup-test.*) rm -rf -- "$test_root" ;; *) exit 1 ;; esac
}
trap cleanup EXIT
mkdir -p "$test_root/bin" "$test_root/app/vendor" "$test_root/app/public"
touch "$test_root/app/vendor/autoload.php"
printf 'APP_KEY=base64:recheck\n' > "$test_root/app/.env"
sed "s|cd /var/www/html|cd $test_root/app|" "$source_root/docker/entrypoint.sh" | sed 's/\r$//' > "$test_root/entrypoint.sh"
cat > "$test_root/bin/php" <<'PHP'
#!/bin/bash
printf '%s\n' "$*" >> "$RECHECK_TRACE"
if [ "${RECHECK_FAIL_MIGRATION:-0}" = 1 ] && [ "$*" = 'artisan migrate --force' ]; then exit 11; fi
PHP
for command in chown chmod; do
    printf '#!/bin/bash\nexit 0\n' > "$test_root/bin/$command"
done
printf '#!/bin/bash\nprintf "SERVER_STARTED\\n" >> "$RECHECK_TRACE"\n' > "$test_root/bin/start-test-server"
chmod +x "$test_root/bin/"*
export PATH="$test_root/bin:$PATH" DB_CONNECTION=mysql
export RECHECK_TRACE="$test_root/success.log"
bash "$test_root/entrypoint.sh" start-test-server > "$test_root/success-output.log"
clear_line="$(grep -n '^artisan config:clear$' "$RECHECK_TRACE" | cut -d: -f1)"
migrate_line="$(grep -n '^artisan migrate --force$' "$RECHECK_TRACE" | cut -d: -f1)"
test "$clear_line" -lt "$migrate_line"
grep -q '^SERVER_STARTED$' "$RECHECK_TRACE"
echo 'PASS: Deployment clears cached configuration before migrations and starts after success.'
export RECHECK_TRACE="$test_root/failure.log" RECHECK_FAIL_MIGRATION=1
if bash "$test_root/entrypoint.sh" start-test-server > "$test_root/failure-output.log"; then
    echo 'FAIL: Deployment ignored a migration failure.'
    exit 1
fi
if grep -q '^SERVER_STARTED$' "$RECHECK_TRACE"; then exit 1; fi
echo 'PASS: A failed migration prevents application startup.'
