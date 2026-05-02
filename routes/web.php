<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeployController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\PaypalController;

use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\FacebookController;


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

Route::controller(PageController::class)->group(function () {
    Route::get('/', 'index')->name('main');
    Route::get('/nosotros', 'us')->name('us');
    Route::get('/reservaciones', 'reservations')->name('reservations');
    Route::get('/contacto', 'contact')->name('contact');
    Route::get('/planes', 'packages')->name('packages');
});

Route::post('/contacto', [FormController::class, 'contact'])->middleware('throttle:5,1')->name('contact');
Route::post('/contactame', [FormController::class, 'contactMe'])->middleware('throttle:5,1')->name('contact.me');



Route::get('home', function () {
    return redirect('/');
});

Route::get('login/facebook', [FacebookController::class, 'login'])->name('login.facebook');
Route::get('login/facebook/callback', [FacebookController::class, 'callback']);

// Deploy hook — protegido por DEPLOY_TOKEN. Permite correr migraciones y cache
// commands desde el browser sin SSH. Si DEPLOY_TOKEN no está configurado,
// el endpoint responde 503 (deshabilitado por seguridad).
Route::get('/deploy/run', [DeployController::class, 'run'])->middleware('throttle:6,1');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\nAllow: /\nDisallow: /administrador/\nDisallow: /cuenta/\nDisallow: /reservaciones/retorno\nDisallow: /api/\n\nSitemap: " . url('/sitemap.xml') . "\n";
    return response($content, 200)->header('Content-Type', 'text/plain');
});

Route::get('/propiedad/{slug}/reservar', [ReservationController::class, 'show'])->name('reservation.show');
Route::post('/api/reservations/quote', [ReservationController::class, 'quote'])->name('api.reservations.quote');
Route::get('/api/properties/{property}/availability', [ReservationController::class, 'availability'])->name('api.properties.availability');
Route::get('/reservaciones/retorno', [ReservationController::class, 'paymentReturn'])->name('reservation.payment.return');

Route::get('/propiedades', [PropertyController::class, 'getProperties'])->name('properties');
Route::get('/propiedad/{slug}', [PropertyController::class, 'getProperty'])->name('property');
Route::get('/propiedades/{slugUser}/propiedad/{slug}', [PropertyController::class, 'getUserProperty'])->name('userProperty');
Route::get('/propiedades/{slug}', [PropertyController::class, 'getPropertiesByUser'])->name('my.properties');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login');
Route::get('/propiedad/{slug}', [PropertyController::class, 'getProperty'])->name('property');
Route::get('/propiedad/imagen/{id}', [PropertyController::class, 'getImageProperty'])->name('imageProperty');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/cuenta/perfil', [UserController::class, 'getProfile'])->name('profile');
    Route::put('/cuenta/perfil/update', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::put('/cuenta/perfil/update/password', [UserController::class, 'updatePassword'])->name('profile.update.password');
    Route::put('/cuenta/perfil/update/photo', [UserController::class, 'updatePhoto'])->name('profile.update.photo');
    Route::get('/cuenta/mis-propiedades', [PropertyController::class, 'getMyProperties'])->name('myProperties');
    Route::get('/cuenta/propiedad/nueva', [PropertyController::class, 'newProperty'])->name('properties.new');
    Route::get('/propiedad/{slug}/editar', [PropertyController::class, 'editProperty'])->name('properties.edit');
    Route::get('/propiedad/{id}/image/{image}/delete', [PropertyController::class, 'deleteImage'])->name('property.delete.image');
    Route::post('/propiedad/{id}/guardar', [PropertyController::class, 'saveProperty'])->name('property.save')->middleware('validate.property');
    Route::post('/propiedad/guardar', [PropertyController::class, 'create'])->name('property.save.new')->middleware('validate.property');
    Route::delete('/propiedad/{slug}/eliminar', [PropertyController::class, 'destroy'])->name('properties.destroy');
    Route::post('/propiedad/{slug}/destacar', [PropertyController::class, 'toggleFeatured'])->name('properties.feature');
    Route::post('/api/properties/ai-description', [PropertyController::class, 'aiDescription'])->name('api.properties.ai_description');

    Route::post('/pagos/paypal', [PaypalController::class, 'pay'])->name('paypal.pay');

    Route::post('/reservaciones', [ReservationController::class, 'store'])->name('reservation.store');
    Route::get('/reservaciones/{reservation}/checkout', [ReservationController::class, 'checkout'])->name('reservation.checkout');
    Route::post('/reservaciones/{reservation}/cancelar', [ReservationController::class, 'cancel'])->name('reservation.cancel');
    Route::post('/reservaciones/{reservation}/review', [ReservationController::class, 'storeReview'])->name('reservation.review.store');
    Route::get('/cuenta/mis-reservaciones', [ReservationController::class, 'myReservations'])->name('my.reservations');
    Route::get('/cuenta/reservas-recibidas', [ReservationController::class, 'hostReservations'])->name('host.reservations');
    Route::get('/cuenta/propiedad/{slug}/calendario', [ReservationController::class, 'hostCalendar'])->name('host.calendar');
    Route::post('/cuenta/propiedad/{slug}/calendario/disponibilidad', [ReservationController::class, 'hostUpdateAvailability'])->name('host.calendar.update');
});

Route::get('/cuenta/salir', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('role:admin')->group(function () {
    Route::get('/administrador', [AdminController::class, 'index'])->name('admin');
    Route::get('/administrador/usuario/crear', [AdminController::class, 'create'])->name('users.create');
    Route::put('/administrador/usuario/nuevo', [AdminController::class, 'new'])->name('users.new');
    Route::get('/administrador/usuario/editar/{id}', [AdminController::class, 'edit'])->name('users.edit');
    Route::put('/administrador/usuario/save/{id}', [AdminController::class, 'save'])->name('users.save');
    Route::delete('/administrador/usuario/eliminar/{id}', [AdminController::class, 'destroy'])->name('users.destroy');
    Route::get('/administrador/usuario/asignar/{user}', [AdminController::class, 'assign'])->name('admin.users.assign');
    Route::put('/administrador/usuario/asignar/{user}', [AdminController::class, 'assignAdd'])->name('admin.users.assign.add');

    Route::get('/administrador/banners', [AdminController::class, 'banners'])->name('admin.banners');
    Route::get('/administrador/banner/crear', [AdminController::class, 'createBanner'])->name('admin.banners.create');
    Route::put('/administrador/banner/nuevo', [AdminController::class, 'newBanner'])->name('admin.banners.new');
    Route::get('/administrador/banner/editar/{id}', [AdminController::class, 'editBanner'])->name('admin.banners.edit');
    Route::put('/administrador/banner/save/{id}', [AdminController::class, 'saveBanner'])->name('admin.banners.save');
    Route::delete('/administrador/banner/eliminar/{id}', [AdminController::class, 'destroyBanner'])->name('admin.banners.destroy');

    Route::get('/administrador/paquetes', [PackageController::class, 'index'])->name('admin.packages.index');
    Route::get('/administrador/paquetes/crear', [PackageController::class, 'create'])->name('admin.packages.create');
    Route::get('/administrador/paquetes/editar/{package}', [PackageController::class, 'edit'])->name('admin.packages.edit');
    Route::put('/administrador/paquetes/nueva', [PackageController::class, 'new'])->name('admin.packages.new');
    Route::put('/administrador/paquetes/save/{package}', [PackageController::class, 'save'])->name('admin.packages.save');
    Route::delete('/administrador/paquetes/eliminar/{package}', [PackageController::class, 'destroy'])->name('admin.packages.destroy');
});

Route::controller(AjaxController::class)->group(function () {
    Route::get('/ajax/zip', 'zip')->name('ajax.zip');
    Route::get('/ajax/state', 'state')->name('ajax.state');
});




Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'loginForm')->name('login')->middleware('my_guest');
    Route::get('/registrarse', 'registerForm')->name('register')->middleware('my_guest');
    Route::post('/registrarse', 'register')->middleware('throttle:5,1')->name('register.post');
});

Route::controller(VerificationController::class)->group(function () {
    Route::get('/email/verify', 'show')->middleware('auth')->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', 'verify')->middleware(['auth', 'signed'])->name('verification.verify');
    Route::post('/email/verification-notification', 'resend')->middleware(['auth', 'throttle:6,1'])->name('verification.resend');
});

Route::controller(MercadoPagoController::class)->group(function () {
    Route::get('/webhook', 'webhook')->name('webhook');
    Route::post('/get-product', 'getProduct')->name('getProduct');
});

Route::get('/user-properties/{user}', [PropertyController::class, 'getUserProperties'])->name('user.properties');
