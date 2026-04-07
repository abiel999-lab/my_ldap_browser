<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Ldap\EntryController;
use App\Http\Controllers\API\Ldap\SchemaController;
use App\Http\Controllers\API\Ldap\TreeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
| Make sure you implement versioning in your API routes.
|
*/

/*
|--------------------------------------------------------------------------
| IP Filter Middleware
|--------------------------------------------------------------------------
*/

Route::middleware('ip.filter')->group(function () {
    Route::post('/deauth', [AuthController::class, 'deauth'])->name('deauth');
    Route::get('/loginas', [AuthController::class, 'getLoginAsList'])->name('api.auth.loginas');
});

/*
|--------------------------------------------------------------------------
| LDAP API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('ldap-admin')->group(function () {
    Route::get('/tree', [TreeController::class, 'index']);

    Route::get('/entry', [EntryController::class, 'show']);
    Route::get('/search', [EntryController::class, 'search']);
    Route::post('/entry', [EntryController::class, 'store']);
    Route::patch('/entry/replace-attributes', [EntryController::class, 'replaceAttributes']);
    Route::patch('/entry/set-attribute', [EntryController::class, 'setAttribute']);
    Route::patch('/entry/add-attribute-values', [EntryController::class, 'addAttributeValues']);
    Route::delete('/entry/delete-attribute', [EntryController::class, 'deleteAttribute']);
    Route::patch('/entry/add-object-classes', [EntryController::class, 'addObjectClasses']);
    Route::patch('/entry/remove-object-classes', [EntryController::class, 'removeObjectClasses']);
    Route::patch('/entry/rename', [EntryController::class, 'rename']);
    Route::delete('/entry', [EntryController::class, 'destroy']);

    Route::get('/schema/rootdse', [SchemaController::class, 'rootDse']);
    Route::get('/schema/object-classes', [SchemaController::class, 'objectClasses']);
    Route::get('/schema/attribute-types', [SchemaController::class, 'attributeTypes']);
});
