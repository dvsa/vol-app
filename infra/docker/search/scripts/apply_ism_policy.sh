#!/bin/bash
set -e

# OpenSearch endpoint - adjust as needed for your environment
OPENSEARCH_HOST="${OPENSEARCH_HOST:-localhost:9200}"

# Path to the ISM policy file
POLICY_FILE="/usr/share/opensearch/config/ism_policies/rollover_policy.json"

# Check if OpenSearch is available
until curl -s "http://${OPENSEARCH_HOST}/_cluster/health?wait_for_status=yellow" > /dev/null; do
  echo "Waiting for OpenSearch to be available..."
  sleep 5
done

# Check if ISM plugin is installed
if ! curl -s "http://${OPENSEARCH_HOST}/_cat/plugins" | grep -q "opensearch-index-management"; then
  echo "Error: OpenSearch Index Management plugin is not installed."
  echo "Please install the plugin using: sudo bin/opensearch-plugin install opensearch-index-management"
  exit 1
fi

# Upload the ISM policy
echo "Uploading ISM policy..."
POLICY_RESPONSE=$(curl -s -X PUT "http://${OPENSEARCH_HOST}/_plugins/_ism/policies/rollover_policy" \
  -H "Content-Type: application/json" \
  -d @"${POLICY_FILE}")

if echo "${POLICY_RESPONSE}" | grep -q "\"acknowledged\":true"; then
  echo "ISM policy uploaded successfully."
else
  echo "Failed to upload ISM policy:"
  echo "${POLICY_RESPONSE}"
  exit 1
fi

# List of indices to apply the policy to
INDICES=(
  "address-*"
  "application-*" 
  "busreg-*"
  "case-*" 
  "irfo-*"
  "licence-*"
  "person-*"
  "psv_disc-*"
  "publication-*"
  "user-*"
  "vehicle_current-*"
  "vehicle_removed-*"
)

# Apply the policy to each index
for index in "${INDICES[@]}"; do
  echo "Applying policy to ${index}..."
  ADD_RESPONSE=$(curl -s -X POST "http://${OPENSEARCH_HOST}/_plugins/_ism/add/${index}" \
    -H "Content-Type: application/json" \
    -d '{"policy_id": "rollover_policy"}')
  
  if echo "${ADD_RESPONSE}" | grep -q "\"failures\":false"; then
    echo "Policy applied successfully to ${index}."
  else
    echo "Warning: Failed to apply policy to ${index}:"
    echo "${ADD_RESPONSE}"
    # Continue with other indices even if one fails
  fi
done

echo "ISM policy application completed."
