<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\ContactEnquiryController;
use App\Http\Controllers\Admin\CustomTourRequestController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DestinationController;
use App\Http\Controllers\Admin\DestinationRegionController;
use App\Http\Controllers\Admin\ExperienceCategoryController;
use App\Http\Controllers\Admin\ExperienceController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\PackageCategoryController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PackageEnquiryController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\TravelStyleController;
use App\Http\Controllers\Admin\WebsiteSettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\ContactEnquiryController as PublicContactEnquiryController;
use App\Http\Controllers\Public\CustomTourController as PublicCustomTourController;
use App\Http\Controllers\Public\DestinationController as PublicDestinationController;
use App\Http\Controllers\Public\ExperienceController as PublicExperienceController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PackageAvailabilityController;
use App\Http\Controllers\Public\PackageController as PublicPackageController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\SitemapController;
use App\Http\Controllers\PublicMediaController;
use Illuminate\Support\Facades\Route;

// Shared hosting providers may disable symbolic links. This route provides a
// safe fallback for files stored on Laravel's public disk when
// PUBLIC_STORAGE_URL=/uploads is configured.
Route::get('/uploads/{path}', PublicMediaController::class)
    ->where('path', '.*')
    ->name('public-media.show');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', fn () => response(
    "User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /login\nSitemap: ".route('sitemap')."\n",
    200,
    ['Content-Type' => 'text/plain; charset=UTF-8']
))->name('robots');

Route::get('/', HomeController::class)->name('home');
Route::get('/experiences', [PublicExperienceController::class, 'index'])->name('experiences.index');
Route::get('/experiences/{experience:slug}', [PublicExperienceController::class, 'show'])->name('experiences.show');
Route::get('/packages', [PublicPackageController::class, 'index'])->name('packages.index');
Route::get('/packages/{package:slug}', [PublicPackageController::class, 'show'])->name('packages.show');
Route::post('/packages/{package:slug}/availability', [PackageAvailabilityController::class, 'store'])->middleware('throttle:5,1')->name('packages.availability.store');
Route::get('/destinations', [PublicDestinationController::class, 'index'])->name('destinations.index');
Route::get('/destinations/{destination:slug}', [PublicDestinationController::class, 'show'])->name('destinations.show');
Route::get('/custom-tours', [PublicCustomTourController::class, 'create'])->name('custom-tours');
Route::post('/custom-tours', [PublicCustomTourController::class, 'store'])->middleware('throttle:5,1')->name('custom-tours.store');
Route::get('/custom-tours/success', [PublicCustomTourController::class, 'success'])->name('custom-tours.success');
Route::get('/about-us', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicContactEnquiryController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');

Route::get('/dashboard', function () {
    if (request()->user()->can('admin.dashboard.view')) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->middleware('permission:admin.dashboard.view')->name('dashboard');

    Route::middleware('permission:users.manage')->group(function () {
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::match(['post', 'put'], 'users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('permission:roles.manage')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::match(['post', 'put'], 'roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::get('destinations', [DestinationController::class, 'index'])->middleware('permission:destinations.view')->name('destinations.index');
    Route::get('destinations/create', [DestinationController::class, 'create'])->middleware('permission:destinations.create')->name('destinations.create');
    Route::post('destinations', [DestinationController::class, 'store'])->middleware('permission:destinations.create')->name('destinations.store');
    Route::get('destinations/{destination}/edit', [DestinationController::class, 'edit'])->middleware('permission:destinations.update')->name('destinations.edit');
    Route::post('destinations/{destination}/save', [DestinationController::class, 'update'])->middleware('permission:destinations.update')->name('destinations.save');
    Route::match(['post', 'put'], 'destinations/{destination}', [DestinationController::class, 'update'])->middleware('permission:destinations.update')->name('destinations.update');
    Route::patch('destinations/{destination}/toggle', [DestinationController::class, 'toggle'])->middleware('permission:destinations.update')->name('destinations.toggle');
    Route::delete('destinations/{destination}', [DestinationController::class, 'destroy'])->middleware('permission:destinations.delete')->name('destinations.destroy');
    Route::patch('destinations/{destination}/restore', [DestinationController::class, 'restore'])->middleware('permission:destinations.delete')->name('destinations.restore');

    Route::get('experiences', [ExperienceController::class, 'index'])->middleware('permission:experiences.view')->name('experiences.index');
    Route::get('experiences/create', [ExperienceController::class, 'create'])->middleware('permission:experiences.create')->name('experiences.create');
    Route::post('experiences', [ExperienceController::class, 'store'])->middleware('permission:experiences.create')->name('experiences.store');
    Route::get('experiences/{experience}/edit', [ExperienceController::class, 'edit'])->middleware('permission:experiences.update')->name('experiences.edit');
    Route::match(['post', 'put'], 'experiences/{experience}', [ExperienceController::class, 'update'])->middleware('permission:experiences.update')->name('experiences.update');
    Route::patch('experiences/{experience}/toggle', [ExperienceController::class, 'toggle'])->middleware('permission:experiences.update')->name('experiences.toggle');
    Route::delete('experiences/{experience}', [ExperienceController::class, 'destroy'])->middleware('permission:experiences.delete')->name('experiences.destroy');
    Route::patch('experiences/{experience}/restore', [ExperienceController::class, 'restore'])->middleware('permission:experiences.delete')->name('experiences.restore');

    Route::get('packages', [PackageController::class, 'index'])->middleware('permission:packages.view')->name('packages.index');
    Route::get('packages/create', [PackageController::class, 'create'])->middleware('permission:packages.create')->name('packages.create');
    Route::post('packages', [PackageController::class, 'store'])->middleware('permission:packages.create')->name('packages.store');
    Route::get('packages/{package}', [PackageController::class, 'show'])->middleware('permission:packages.update')->name('packages.show');
    Route::get('packages/{package}/edit', [PackageController::class, 'edit'])->middleware('permission:packages.update')->name('packages.edit');
    Route::post('packages/{package}/save', [PackageController::class, 'update'])->middleware('permission:packages.update')->name('packages.save');
    Route::match(['post', 'put'], 'packages/{package}', [PackageController::class, 'update'])->middleware('permission:packages.update')->name('packages.update');
    Route::patch('packages/{package}/toggle', [PackageController::class, 'toggle'])->middleware('permission:packages.update')->name('packages.toggle');
    Route::delete('packages/{package}', [PackageController::class, 'destroy'])->middleware('permission:packages.delete')->name('packages.destroy');
    Route::patch('packages/{package}/restore', [PackageController::class, 'restore'])->middleware('permission:packages.delete')->name('packages.restore');

    Route::get('package-enquiries', [PackageEnquiryController::class, 'index'])->middleware('permission:enquiries.view')->name('package-enquiries.index');
    Route::get('package-enquiries/export', [PackageEnquiryController::class, 'export'])->middleware('permission:enquiries.view')->name('package-enquiries.export');
    Route::get('package-enquiries/{packageEnquiry}', [PackageEnquiryController::class, 'show'])->middleware('permission:enquiries.view')->name('package-enquiries.show');
    Route::match(['post', 'put'], 'package-enquiries/{packageEnquiry}', [PackageEnquiryController::class, 'update'])->middleware('permission:enquiries.update')->name('package-enquiries.update');

    Route::get('custom-tour-requests', [CustomTourRequestController::class, 'index'])->middleware('permission:custom_tours.view')->name('custom-tour-requests.index');
    Route::get('custom-tour-requests/export', [CustomTourRequestController::class, 'export'])->middleware('permission:custom_tours.view')->name('custom-tour-requests.export');
    Route::get('custom-tour-requests/{customTourRequest}', [CustomTourRequestController::class, 'show'])->middleware('permission:custom_tours.view')->name('custom-tour-requests.show');
    Route::match(['post', 'put'], 'custom-tour-requests/{customTourRequest}', [CustomTourRequestController::class, 'update'])->middleware('permission:custom_tours.update')->name('custom-tour-requests.update');

    Route::get('contact-enquiries', [ContactEnquiryController::class, 'index'])->middleware('permission:enquiries.view')->name('contact-enquiries.index');
    Route::get('contact-enquiries/{contactEnquiry}', [ContactEnquiryController::class, 'show'])->middleware('permission:enquiries.view')->name('contact-enquiries.show');
    Route::match(['post', 'put'], 'contact-enquiries/{contactEnquiry}', [ContactEnquiryController::class, 'update'])->middleware('permission:enquiries.update')->name('contact-enquiries.update');
    Route::delete('contact-enquiries/{contactEnquiry}', [ContactEnquiryController::class, 'destroy'])->middleware('permission:enquiries.update')->name('contact-enquiries.destroy');

    Route::get('settings', [WebsiteSettingController::class, 'edit'])->middleware('permission:settings.manage')->name('settings.index');
    Route::match(['post', 'put'], 'settings', [WebsiteSettingController::class, 'update'])->middleware('permission:settings.manage')->name('settings.update');

    Route::get('pages', [PageSectionController::class, 'index'])->middleware('permission:pages.manage')->name('pages.index');
    Route::get('pages/create', [PageSectionController::class, 'create'])->middleware('permission:pages.manage')->name('pages.create');
    Route::post('pages', [PageSectionController::class, 'store'])->middleware('permission:pages.manage')->name('pages.store');
    Route::get('pages/{page}/edit', [PageSectionController::class, 'edit'])->middleware('permission:pages.manage')->name('pages.edit');
    Route::match(['post', 'put'], 'pages/{page}', [PageSectionController::class, 'update'])->middleware('permission:pages.manage')->name('pages.update');
    Route::delete('pages/{page}', [PageSectionController::class, 'destroy'])->middleware('permission:pages.manage')->name('pages.destroy');

    foreach ([['testimonials', TestimonialController::class, 'testimonials.manage'], ['team-members', TeamMemberController::class, 'team.manage'], ['faqs', FaqController::class, 'faqs.manage']] as [$uri, $controller, $permission]) {
        Route::get($uri, [$controller, 'index'])->middleware("permission:{$permission}")->name("{$uri}.index");
        Route::get("{$uri}/create", [$controller, 'create'])->middleware("permission:{$permission}")->name("{$uri}.create");
        Route::post($uri, [$controller, 'store'])->middleware("permission:{$permission}")->name("{$uri}.store");
        Route::get("{$uri}/{id}/edit", [$controller, 'edit'])->middleware("permission:{$permission}")->name("{$uri}.edit");
        Route::match(['post', 'put'], "{$uri}/{id}", [$controller, 'update'])->middleware("permission:{$permission}")->name("{$uri}.update");
        Route::delete("{$uri}/{id}", [$controller, 'destroy'])->middleware("permission:{$permission}")->name("{$uri}.destroy");
        Route::patch("{$uri}/{id}/restore", [$controller, 'restore'])->middleware("permission:{$permission}")->name("{$uri}.restore");
    }

    $taxonomies = [
        ['destination-regions', DestinationRegionController::class, 'destinations'],
        ['experience-categories', ExperienceCategoryController::class, 'experiences'],
        ['travel-styles', TravelStyleController::class, 'experiences'],
        ['package-categories', PackageCategoryController::class, 'packages'],
    ];

    foreach ($taxonomies as [$uri, $controller, $permission]) {
        Route::get($uri, [$controller, 'index'])->middleware("permission:{$permission}.view")->name("{$uri}.index");
        Route::get("{$uri}/create", [$controller, 'create'])->middleware("permission:{$permission}.create")->name("{$uri}.create");
        Route::post($uri, [$controller, 'store'])->middleware("permission:{$permission}.create")->name("{$uri}.store");
        Route::get("{$uri}/{id}/edit", [$controller, 'edit'])->middleware("permission:{$permission}.update")->name("{$uri}.edit");
        Route::match(['post', 'put'], "{$uri}/{id}", [$controller, 'update'])->middleware("permission:{$permission}.update")->name("{$uri}.update");
        Route::patch("{$uri}/{id}/toggle", [$controller, 'toggle'])->middleware("permission:{$permission}.update")->name("{$uri}.toggle");
        Route::delete("{$uri}/{id}", [$controller, 'destroy'])->middleware("permission:{$permission}.delete")->name("{$uri}.destroy");
        Route::patch("{$uri}/{id}/restore", [$controller, 'restore'])->middleware("permission:{$permission}.delete")->name("{$uri}.restore");
    }
});
