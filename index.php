<?php 

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once('includes.php');

// Routing namespace
use Steampixel\Route;

// dbadmin
if (Setting::getValue("enable_dbadmin")) {
    Route::add('/dbadmin', function() {
        include('dbadmin/index.php');
    }, ['GET', 'POST']);    
}

// include api routes
include('api/routes.php');

// Run the router
Route::run('/', false, false, true);