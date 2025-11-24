<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DialplanController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\GateWayController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupPermissionController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccessControlController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BillingDealController;
use App\Http\Controllers\BillingInvoiceController;
use App\Http\Controllers\BridgeController;
use App\Http\Controllers\CallBlockController;
use App\Http\Controllers\CallCenterAgentController;
use App\Http\Controllers\CallCenterQueueController;
use App\Http\Controllers\CarrierController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceProfileController;
use App\Http\Controllers\DeviceVendorController;
use App\Http\Controllers\DialplanBuilderController;
use App\Http\Controllers\EmailQueueController;
use App\Http\Controllers\FaxController;
use App\Http\Controllers\IVRMenuController;
use App\Http\Controllers\LcrController;
use App\Http\Controllers\UserGroupController;
use App\Http\Controllers\ModFormatCDRController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModXMLCURLController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\RegistrationsController;
use App\Http\Controllers\MusicOnHoldController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PhraseController;
use App\Http\Controllers\RingGroupController;
use App\Http\Controllers\XmlCDRController;
use App\Http\Controllers\SipProfileController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\UserActivationController;
use App\Http\Controllers\ViewCallRecordingController;
use App\Http\Middleware\Authenticate;
use App\Livewire\DialplanBuilderDemo;
use App\Models\AccessControl;
use App\Models\Destination;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::redirect('/', '/login');

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'index'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/login/okta', [AuthController::class, 'redirectToProvider'])->name('login-okta');
    Route::get('/login/okta/callback', [AuthController::class, 'handleProviderCallback']);
});

Route::middleware(['auth','permission'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

    // BILLING
    Route::get('/billing/{billing}/{paymentGateway}/success', [BillingController::class, 'paymentSuccess'])->name('billing.success', 'billing.success');
    Route::get('/billing/{billing}/{paymentGateway}/cancel', [BillingController::class, 'paymentCancel'])->name('billing.cancel', 'billing.cancel');
    Route::match(['get', 'post'], '/billing/analysis', [BillingController::class, 'analysis'])->name('billing.analysis', 'billing.analysis');
    Route::get('/billing/pricing', [BillingController::class, 'pricing'])->name('billing.pricing', 'billing.pricing');
    Route::resource('/billing/deals', BillingDealController::class)->names('billing.deals')->parameters(["deals" => "billingDeal"]);
    Route::get('/billing/{billing}/export', [BillingController::class, 'export'])->name('billing.export', 'billing.export');
    Route::get('/billing/{billing}/payment', [BillingController::class, 'payment'])->name('billing.payment', 'billing.payment');
    Route::get('/billing/{billing}/transfer', [BillingController::class, 'transferGet'])->name('billing.transfer_get', 'billing.transfer_get');
    Route::post('/billing/{billing}/transfer', [BillingController::class, 'transferPost'])->name('billing.transfer_post', 'billing.transfer_post');
    Route::get('/billing/{billing}/{paymentGateway}/create', [BillingController::class, 'paymentCreate'])->name('billing.payment.create', 'billing.payment.create');
    Route::post('/billing/{billing}/{paymentGateway}/store', [BillingController::class, 'paymentStore'])->name('billing.payment.store', 'billing.payment.store');
    Route::get('/billing/{billing}/view', [BillingController::class, 'view'])->name('billing.view', 'billing.view');
    Route::post('/billing/{billingInvoice}/process', [BillingInvoiceController::class, 'process'])->name('billing.process', 'billing.process');
    Route::resource('/billing', BillingController::class)->name('billing', 'billing');

    // BRIDGE
    Route::resource('/bridges', BridgeController::class)->name('bridges', 'bridges');

    //CALL RECORDINGS
    Route::get('/callrecordings', [ViewCallRecordingController::class, 'index'])->name('callrecordings.index', 'callrecordings.index');
    Route::get('/callrecordings/{file}/play', [ViewCallRecordingController::class, 'play'])->name('callrecordings.play', 'callrecordings.play');
    Route::get('/callrecordings/{file}/download', [ViewCallRecordingController::class, 'download'])->name('callrecordings.download', 'callrecordings.download');
    Route::get('/callrecordings/{xmlcdr}/details', [ViewCallRecordingController::class, 'details'])->name('callrecordings.details');

    // DESTINATION
    Route::get('destinations/import', [DestinationController::class, 'import'])->name('destinations.import');
    Route::get('/destinations/export', [DestinationController::class, 'export'])->name('destinations.export', 'destinations.export');
    Route::resource('/destinations', DestinationController::class)->name('destinations', 'destinations');

    // DIALPLAN
    Route::resource('/dialplans', DialplanController::class)->name('dialplans', 'dialplans');
    Route::get('/dialplans/inbound/create', [DialplanController::class, 'createInbound'])->name('dialplans.inbound.create');
    Route::post('/dialplans/inbound/store', [DialplanController::class, 'storeInbound'])->name('dialplans.inbound.store');
    Route::get('/dialplans/outbound/create', [DialplanController::class, 'createOutbound'])->name('dialplans.outbound.create');
    Route::post('/dialplans/outbound/store', [DialplanController::class, 'storeOutbound'])->name('dialplans.outbound.store');

    // DOMAIN
    Route::resource('/domains', DomainController::class)->name('domains', 'domains');
    Route::post('/domains/switch', [DomainController::class, 'switch'])->name('domain.switch');
    Route::get('/domains/switch', function () {
        return redirect('/dashboard');
    });
    Route::get('/domains/switch/{domain}', [DomainController::class, 'switchByUuid'])->name('domain.switchuuid');

    // FAX
    Route::resource('/faxes', FaxController::class)->name('faxes', 'faxes');
    Route::get('/faxes/{fax}/send', [FaxController::class, 'send'])->name('faxes.send');

    // GROUP
    Route::resource('/groups', GroupController::class)->name('groups', 'groups');
    Route::get('/groups/{group}/copy', [GroupController::class, 'copy'])->name('groups.copy');

    // PERMISSION
    //Route::resource('/permissions', PermissionController::class)->name('permissions', 'permissions');
    #Route::get('/permissions', [GroupPermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions', [GroupPermissionController::class, 'index'])->name('permissions.all');
    Route::get('/groups/{groupUuid}/permissions', [GroupPermissionController::class, 'index'])->name('permissions.index');
    //Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    //Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::put('/permissions/{groupUuid}', [GroupPermissionController::class, 'update'])->name('permissions.update');
    Route::patch('/permissions/{groupUuid}', [GroupPermissionController::class, 'update'])->name('permissions.update');
    //Route::get('/permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show');
    #Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    //Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
    //Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');

    Route::get('/permissions/create', [GroupPermissionController::class, 'create'])->name('permissions.create');
    Route::get('/permissions/{permissionUuid}/edit', [GroupPermissionController::class, 'edit'])->name('permissions.edit');

    // GATEWAY
    Route::resource('/gateways', GateWayController::class)->name('gateways', 'gateways');
    Route::get('/gateways/{gateway}/copy', [GateWayController::class, 'copy'])->name('gateways.copy');

    // CALL BLOCKS
    Route::resource('/callblocks', CallBlockController::class)->name('callblocks', 'callblocks');
    Route::post('/callblocks/block', [CallBlockController::class, 'block'])->name('callblocks.block', 'callblocks.block');

    // CARRIERS
    Route::resource('/carriers', CarrierController::class)->name('carriers', 'carriers');

    //LCR
    Route::get('/lcr/export', [LcrController::class, 'export'])->name('lcr.export', 'lcr.export');
    Route::post('/lcr/import', [LcrController::class, 'import'])->name('lcr.import', 'lcr.import');
    Route::post('/lcr/checkrate', [LcrController::class, 'checkrate'])->name('lcr.checkrate');
    Route::resource('/lcr', LcrController::class)->name('lcr', 'lcr');

    // IVR MENU
    Route::resource('/ivr_menu', IVRMenuController::class)->name('ivr_menu', 'ivr_menu');

    // MODULES
    Route::get('/modules/{module}/start', [ModuleController::class, 'start'])->name('modules.start');
    Route::get('/modules/{module}/stop', [ModuleController::class, 'stop'])->name('modules.stop');
    Route::post('/modules/bulk', [ModuleController::class, 'bulk'])->name('modules.bulk');
    Route::resource('/modules', ModuleController::class)->name('modules', 'modules');

    // PHRASE
    Route::resource('/phrases', PhraseController::class)->name('phrases', 'phrases');

    // SIP PROFILE
    Route::resource('/sipprofiles', SipProfileController::class)->name('sipprofiles', 'sipprofiles');
    Route::get('/sipprofiles/{sipprofile}/copy', [SipProfileController::class, 'copy'])->name('sipprofiles.copy');


    // MENU
    Route::resource('/menus', MenuController::class)->name('menus', 'menus');

    // MENU ITEM
//    Route::resource('/menuitems', MenuItemController::class)->name('menuitems', 'menuitems');
    Route::get('/menu/{menu}/menuitem/{menuitem}/edit', [MenuItemController::class, 'edit'])->name('menuitems.edit');
    Route::get('/menu/{menu}/menuitems', [MenuItemController::class, 'index'])->name('menuitems.index');
    Route::put('/menu/{menu}/menuitem/{menuitem}', [MenuItemController::class, 'update'])->name('menuitems.update');
    Route::patch('/menu/{menu}/menuitem/{menuitem}', [MenuItemController::class, 'update'])->name('menuitems.update');
    Route::delete('/menu/{menu}/menuitem/{menuitem}', [MenuItemController::class, 'destroy'])->name('menuitems.destroy');
    Route::get('/menu/{menu}/menuitems/create', [MenuItemController::class, 'create'])->name('menuitems.create');
    Route::post('/menu/{menu}/menuitems', [MenuItemController::class, 'store'])->name('menuitems.store');

    // MUSIC ON HOLD
    Route::resource('/musiconhold', MusicOnHoldController::class)->name('musiconhold', 'musiconhold');
    Route::get('/musiconhold/{musiconhold}/{file}/play', [MusicOnHoldController::class, 'play'])->name('musiconhold.play');
    Route::get('/musiconhold/{musiconhold}/{file}/download', [MusicOnHoldController::class, 'download'])->name('musiconhold.download');
    Route::post('/musiconhold/upload', [MusicOnHoldController::class, 'upload'])->name('musiconhold.upload');

    // STREAMS
    Route::resource('/streams', StreamController::class)->name('streams', 'streams');

    // USERS
    Route::resource('/users', UserController::class)->name('users', 'users');

    // USER GROUP
    Route::get('/groups/{group}/members', [UserGroupController::class, 'index'])->name('usergroup.index');
    Route::put('/groups/{group}/members', [UserGroupController::class, 'update'])->name('usergroup.update');

    // ACCESS CONTROL
    Route::resource('/accesscontrol', AccessControlController::class)->name('accesscontrol', 'accesscontrol');
    Route::get('/accesscontrol/{accesscontrol}/copy', [AccessControlController::class, 'copy'])->name('accesscontrol.copy');

    // XML CDR
    Route::get('/xmlcdr', [XmlCDRController::class, 'index'])->name('xmlcdr.index');
    Route::get('/xmlcdr/{xmlcdr}/play', [XmlCDRController::class, 'play'])->name('xmlcdr.play');
    Route::get('/xmlcdr/{xmlcdr}/download', [XmlCDRController::class, 'download'])->name('xmlcdr.download');

    Route::resource('registrations', RegistrationsController::class)->name('registrations', 'registrations');

    Route::resource('/contacts', ContactController::class);
    Route::get('/contacts/{uuid}/vcard', [ContactController::class, 'exportVCard'])->name('contacts.vcard');

    Route::resource('/extensions', ExtensionController::class)->except('show');
    Route::get('extensions/import', [ExtensionController::class, 'import'])->name('extensions.import');
    Route::get('extensions/export', [ExtensionController::class, 'export'])->name('extensions.export');

    Route::resource('/devices', DeviceController::class)->except('show');
    Route::resource('/devices/devices_profiles', DeviceProfileController::class)->except('show');
    Route::resource('/devices/devices_vendors', DeviceVendorController::class);
    Route::get('devices/import', [DeviceController::class, 'import'])->name('devices.import');
    Route::get('devices/export', [DeviceController::class, 'export'])->name('devices.export');

    Route::resource('/email-queues', EmailQueueController::class);
    Route::resource('ring_groups', RingGroupController::class)->name('ringgroups', 'ringgroups');
    Route::resource('/call_center_queues', CallCenterQueueController::class)->except('show');
    Route::resource('/call_center_agent', CallCenterAgentController::class)->except('show');
    Route::get('/call_center_agent_status', [CallCenterAgentController::class,'showStatus'])->name('callCenterAgentStatus');

    Route::get('/dialplan-demo', [DialplanBuilderController::class, 'demo'])
        ->name('dialplan.demo');

    Route::get('/dialplan/{uuid}/flow', [DialplanBuilderController::class, 'show'])
        ->name('dialplan.flow.show');



});

Route::post('/switch/xml_handler/{binding}', function (Request $request, string $binding){
    $xml = new ModXMLCURLController;
    $allowedMethods = ['configuration', 'directory', 'dialplan', 'languages'];

    if(!in_array($binding, $allowedMethods)){
        return response('Method not allowed', 403)->header('Content-Type', 'text/xml');
    }

    return response($xml->$binding($request), 200)->header('Content-Type', 'text/xml');
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post("/switch/format_cdr", [ModFormatCDRController::class, 'store'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
