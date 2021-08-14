<?php

namespace Deployer;

require 'recipe/laravel.php';

// Project name
set('application', 'laravel-agil-api');

// Project repository
set('repository', 'git@github.com:fendysketsa/laravel-api-agil.git');
set('branch', 'master');
// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

set('ssh_multiplexing', false);

//set('writable_mode', 'chown');

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', []);
set('http_user', 'fendy');

// Hosts

host('35.198.251.148')
    ->user('fendy')
    ->identityFile('/home/fcn/.ssh/id_rsa')
    ->set('deploy_path', '/var/www/html/jatisky.com/public_html');

// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});
// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

//before('deploy:symlink', 'artisan:migrate');
