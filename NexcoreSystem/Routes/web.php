<?php

/*
|--------------------------------------------------------------------------
| NexCore System Routes
|--------------------------------------------------------------------------
| Prefix: /nexcore/system
| Name: nexcore.system.
| Middleware: web, auth (requires login)
*/

// System Master Page
Route::view('/system_master_page', 'nexcore_system::system_master_page')->name('system-master-page');
