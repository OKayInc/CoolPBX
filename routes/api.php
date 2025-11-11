<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\API\CallCenterAgentAPIController;;
use App\Http\Controllers\API\DomainAPIController;
use App\Http\Controllers\API\ExtensionAPIController;
use App\Http\Controllers\API\UserAPIController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EmailQueueController;
use App\Http\Controllers\FileURLRouteController;
use App\Http\Controllers\UserActivationController;
use App\Http\Middleware\VerifyAuthenticationKey;
use App\Http\Requests\UserRequest;
use App\Models\Extension;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/activate-user', [UserActivationController::class, 'activateUser']);
Route::get('/contacts/search', [ContactController::class, 'search']);

Route::post('/authenticate', [AuthController::class, 'apiLogin']);

Route::post('/billing/{billing}/{paymentGateway}/notification', [BillingController::class, 'paymentNotification'])->name('billing.notification', 'billing.notification');

Route::middleware(VerifyAuthenticationKey::class)->group(function () {
    Route::get('/my/agents', [CallCenterAgentAPIController::class, 'mine']);
    Route::get('/my/domains', [DomainAPIController::class, 'mine']);
    Route::get('/my/extensions', [ExtensionAPIController::class, 'mine']);
    Route::get('/my/user', [UserAPIController::class, 'mine']);
});

Route::middleware([VerifyAuthenticationKey::class, 'permission'])->group(function () {
    Route::get('/domains', [DomainAPIController::class, 'index'])->name('domains.all');
    Route::get('/extensions', [ExtensionAPIController::class, 'index'])->name('extensions.all');
    Route::post('/email/test', [EmailQueueController::class, 'testEmail']);
});

Route::get("/fs/{path?}", [FileURLRouteController::class, "handle"])->where("path", ".*")->name("fileurlroute.handle");
