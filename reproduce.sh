#!/usr/bin/env bash
set -euo pipefail

MAX=10

for i in $(seq 1 "$MAX"); do
  echo "--- Attempt $i/$MAX ---"
  rm -rf vendor/pestphp/pest-plugin-type-coverage/.temp

  output=$(./vendor/bin/pest --type-coverage --compact --min=50 2>&1) && rc=0 || rc=$?

  if [ "$rc" -eq 0 ]; then
    continue
  fi

  # Check if the failure is the race condition (cache file corruption)
  if echo "$output" | grep -q "ParseError"; then
    echo "$output"
    echo ""
    echo "*** Race condition triggered on attempt $i! ***"
    exit 1
  fi

  # Normal test failure — not the race condition
  echo "$output"
  echo ""
  echo "Test failed on attempt $i but not due to race condition (normal failure)."
done

echo "No race condition after $MAX attempts."
