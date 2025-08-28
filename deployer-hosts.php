<?php

namespace Deployer;

host('forger')
    ->setHostname(getenv('SSH_HOST'))
    ->setRemoteUser(getenv('SSH_USER'))
    ->setPort(getenv('SSH_PORT', 22))
    ->setDeployPath(getenv('SSH_PATH', '/var/www/deployer'));

// host does not allow GHA whitelisting so we use a jump box (ssh config built in GHA)
host('forger-jump')
    ->setHostname('forger-jump')
    ->setRemoteUser(getenv('SSH_USER'))
    ->setDeployPath(getenv('SSH_PATH', '/var/www/deployer'));
