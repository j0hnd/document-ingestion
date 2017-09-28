<?php

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Batch status
|--------------------------------------------------------------------------
|
*/
defined('BATCH_STATUS_PENDING') || define('BATCH_STATUS_PENDING', 'Pending');
defined('BATCH_STATUS_READY') || define('BATCH_STATUS_READY', 'Ready');
defined('BATCH_STATUS_PROCESSING') || define('BATCH_STATUS_PROCESSING', 'Processing');
defined('BATCH_STATUS_REVIEWED') || define('BATCH_STATUS_REVIEWED', 'Reviewed');
defined('BATCH_STATUS_REJECTED') || define('BATCH_STATUS_REJECTED', 'Rejected');
defined('BATCH_STATUS_REMOVED') || define('BATCH_STATUS_REMOVED', 'Removed');
defined('BATCH_STATUS_COMPLETED') || define('BATCH_STATUS_COMPLETED', 'Completed');
defined('BATCH_STATUS_HELD') || define('BATCH_STATUS_HELD', 'Held');
defined('BATCH_STATUS_SEND') || define('BATCH_STATUS_SEND', 'Send');

/*
|--------------------------------------------------------------------------
| Document status
|--------------------------------------------------------------------------
|
*/
defined('DOCUMENT_STATUS_PENDING') || define('DOCUMENT_STATUS_PENDING', 'Pending');
defined('DOCUMENT_STATUS_REJECT') || define('DOCUMENT_STATUS_REJECT', 'Rejected');
defined('DOCUMENT_STATUS_ACCEPT') || define('DOCUMENT_STATUS_ACCEPT', 'Accepted');
defined('DOCUMENT_STATUS_SEND') || define('DOCUMENT_STATUS_SEND', 'Send');


/*
|--------------------------------------------------------------------------
| Batch source
|--------------------------------------------------------------------------
|
*/
defined('BATCH_SOURCE_WEB') || define('BATCH_SOURCE_WEB', 'Web');
defined('BATCH_SOURCE_EMAIL') || define('BATCH_SOURCE_EMAIL', 'Email');
defined('BATCH_SOURCE_CLOUD') || define('BATCH_SOURCE_CLOUD', 'Cloud');

/*
|--------------------------------------------------------------------------
| System logs action
|--------------------------------------------------------------------------
|
*/
defined('SYSTEM_LOGS_ACTION_INSERT') || define('SYSTEM_LOGS_ACTION_INSERT', 'Insert');
defined('SYSTEM_LOGS_ACTION_UPDATE') || define('SYSTEM_LOGS_ACTION_UPDATE', 'Update');
defined('SYSTEM_LOGS_ACTION_DELETE') || define('SYSTEM_LOGS_ACTION_DELETE', 'Delete');
defined('SYSTEM_LOGS_ACTION_ERROR') || define('SYSTEM_LOGS_ACTION_ERROR', 'Error');

/*
|--------------------------------------------------------------------------
| System logs source
|--------------------------------------------------------------------------
|
*/
defined('SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED') || define('SYSTEM_LOGS_SOURCE_SYSTEM_GENERATED', 'System Generated');
defined('SYSTEM_LOGS_SOURCE_USER_INPUT') || define('SYSTEM_LOGS_SOURCE_USER_INPUT', 'User Input');


/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Include The Compiled Class File
|--------------------------------------------------------------------------
|
| To dramatically increase your application's performance, you may use a
| compiled class file which contains all of the classes commonly used
| by a request. The Artisan "optimize" is used to create this file.
|
*/

$compiledPath = __DIR__.'/../vendor/compiled.php';

if (file_exists($compiledPath))
{
	require $compiledPath;
}
