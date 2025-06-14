#!/bin/bash
set -e
set -o pipefail

if [[ "${GITHUB_ACTIONS}" == "true" ]]; then
    echo "Running in GitHub Actions - using environment variables from secrets"
else
    echo "Running locally - sourcing deployment configuration"
    if [[ ! -f .env.deploy ]]; then
        echo "Error: .env.deploy file not found"
        echo "Create .env.deploy with your production values when using local deployments"
        exit 1
    fi

    set -a
    source .env.deploy
    set +a
fi

echo "Deploying to production..."
sshpass -e dep forger:deploy forger
echo "Deployment complete!"
