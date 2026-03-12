# pest-plugin-type-coverage cache race condition reproduction

Minimal reproduction for a cache file corruption bug in `pestphp/pest-plugin-type-coverage` v4.0.3.

## Setup

```bash
composer install
php generate.php          # creates ~300 classes with untyped params
```

## Reproduce

```bash
./reproduce.sh
```

The expected error:

```
ParseError
syntax error, unexpected single-quoted string " => "
at vendor/pestphp/pest-plugin-type-coverage/.temp/v3.php
```

## Workaround

Disable forking:

```bash
php -d variables_order=EGPCS vendor/bin/pest --type-coverage --compact --min=50
# with __PEST_PLUGIN_ENV=testing in env
```

## Root Cause

`Cache::persist()` in `src/Support/Cache.php` does a read-modify-write on a shared
file (`.temp/v3.php`) from multiple pokio-forked processes. The `flock(LOCK_NB)` with
100 retries silently falls back to no-lock, allowing concurrent writes that corrupt
the `var_export()` output.
