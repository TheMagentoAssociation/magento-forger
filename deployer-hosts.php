<?php

namespace Deployer;

host('forger')
    ->setHostname(getenv('SSH_HOST'))
    ->setRemoteUser(getenv('SSH_USER'))
    ->setPort(getenv('SSH_PORT', 22))
    ->setDeployPath(getenv('SSH_PATH', '/var/www/deployer'));

