#!/bin/bash
set -e
set -o pipefail

DEPLOY_HOST="forger"
if [[ "${GITHUB_ACTIONS}" == "true" ]]; then
    echo "Running in GitHub Actions - using environment variables from secrets"
    DEPLOY_HOST="forger-jump"
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
sshpass -e dep forger:deploy -v $DEPLOY_HOST
echo "Deployment complete!"
