<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config
set('repository', 'git@github.com:TheMagentoAssociation/magento-forger.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('jetrails')
    ->set('hostname', '69.27.41.78')
    ->set('remote_user', 'forge-74pb0')
    ->set('deploy_path', '~/forger.magento-opensource.com/html');

// Hooks

after('deploy:failed', 'deploy:unlock');
