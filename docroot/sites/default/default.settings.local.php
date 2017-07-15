if (isset($_SERVER['RDS_HOSTNAME'])) {
  $settings['memcache']['servers'] = ['127.0.0.1:11211' => 'default'];
  $settings['cache']['bins']['render'] = 'cache.backend.memcache';
  $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.memcache';

  $databases['default']['default'] = array(
    'database' => $_SERVER['RDS_DB_NAME'],
    'username' => $_SERVER['RDS_USERNAME'],
    'password' => $_SERVER['RDS_PASSWORD'],
    'prefix' => '',
    'host' => $_SERVER['RDS_HOSTNAME'],
    'port' => $_SERVER['RDS_PORT'],
    'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    'driver' => 'mysql',
  );
}
