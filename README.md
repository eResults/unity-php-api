unity-php-api
=============
Use this API to connect your application with eResults ID. You need an API key and an account system name.

## Example
```php
include vendor/unity-php-api/API.php

define( 'API_KEY', 'XXXX@!#XXXX' );
define( 'ACCOUNT_SYSTEM_NAME', 'x123' );

session_start();

$api = new API( ACCOUNT_SYSTEM_NAME, API_KEY );

$currentUser = $api->getAuthorizedUser();

if ( ! $currentUser )
{
  $api->login();
}

echo 'Welcom ' . $currentUser[ 'givenNames' ];
```
