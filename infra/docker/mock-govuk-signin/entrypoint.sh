#!/bin/sh

if [ "$LOCAL_PROXY" = "true" ]; then
  echo "Starting in local proxy mode..."
  
  # Start RIE in background with Lambda handler
  echo "Starting RIE with handler: dist/lambda.handler"
  /lambda-entrypoint.sh dist/lambda.handler &
  
  # Wait for RIE to be ready
  echo "Waiting for Lambda RIE to start..."
  sleep 3
  
  # Start proxy in foreground
  echo "Starting API Gateway proxy..."
  exec node dist/index.js
else
  # Production Lambda mode
  echo "Starting in Lambda mode..."
  exec /lambda-entrypoint.sh dist/lambda.handler
fi