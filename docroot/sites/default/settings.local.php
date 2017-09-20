<?php

//D6 DB config
$db_url = 'mysqli://drupaluser@127.0.0.1:33067/ausgovhack_prod';

//D7 DB config
if (!isset($databases))
  $databases = array();

$databases['default']['default'] = array(
  'driver' => 'mysql',
  'database' => 'ausgovhack_prod',
  'username' => 'drupaluser',
  'password' => '',
  'host' => '127.0.0.1',
  'port' => 33067 );

if (empty($config_directories['active']))
  $config_directories['active'] = "sites/default/files/config_2ba73f0cde85ba547e2fe9b1d0f6f194552f061d/active";
if (empty($config_directories['staging']))
  $config_directories['staging'] = "sites/default/files/config_2ba73f0cde85ba547e2fe9b1d0f6f194552f061d/staging";

if (empty($settings['hash_salt']))
  $settings['hash_salt'] = 'ZGVmYXVsdAAAAAEAAAAAAAAAAADAgmoBAAAAAA==';
