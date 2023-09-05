<?php

namespace Deployer;

require 'recipe/symfony.php';
require 'contrib/webpack_encore.php';

// Config

set('repository', 'git@github.com:IGB-CNRG/Connect.git');

add('shared_files', []);
add('shared_dirs', ['public/media', 'public/uploads']);
add('writable_dirs', ['public/media', 'public/uploads']);

// Hosts
import('deploy-hosts.yml');

// Tasks

// Hooks

after('deploy:failed', 'deploy:unlock');

after('deploy:vendors', 'yarn:install');
after('yarn:install', 'webpack_encore:build');
before('deploy:symlink', 'database:migrate');