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

try
{
  $currentUser = $api->getAuthorizedUser();
}
catch ( \Unity\Exceptions\Forbidden $e )
{
  // Current user doesn't have rights for this application, send user to login page
  $api->login();
}
catch ( \Unity\Exceptions\SessionExpired $e )
{
  // Users session is expired, send user to login page to login again
  $api->login();
}
catch( \Unity\Exceptions\UnAuthorized $e )
{
  // No access to this account, send user to login page
  $api->log()t;
}
catch( \Unity\Exceptions\BadRequest $e )
{
  echo "Request failed because of the following reason: $e->getMessage()";
}

if ( ! $currentUser )
{
  $api->login();
}

echo 'Welcom ' . $currentUser[ 'givenNames' ];
```
