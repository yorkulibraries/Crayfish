<?php

require_once __DIR__.'/../vendor/autoload.php';

use Islandora\Crayfish\Commons\Provider\IslandoraServiceProvider;
use Islandora\Crayfish\Commons\Provider\YamlConfigServiceProvider;
use Islandora\Lameduck\Controller\LameduckController;
use Silex\Application;

$app = new Application();

$app->register(new IslandoraServiceProvider());
$app->register(new YamlConfigServiceProvider(__DIR__ . '/../cfg/config.yaml'));

$app['lameduck.controller'] = function ($app) {
    return new LameduckController(
        $app['crayfish.cmd_execute_service'],
        $app['crayfish.lameduck.executable'],
        $app['monolog']
    );
};

$app->get('/lameduck/{fedora_resource}', "lameduck.controller:lame")
    ->assert('fedora_resource', '.+')
    ->convert('fedora_resource', 'crayfish.fedora_resource:convert');

return $app;
