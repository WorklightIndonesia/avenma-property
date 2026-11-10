<?php

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\DeveloperController;
use App\Http\Controllers\Admin\ListingSettingController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WhatsAppTemplateController;
use App\Http\Controllers\Admin\WilayahController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\LayananController;
use App\Http\Controllers\Public\ListingController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('public.pages.home');
Route::get('/kontak', [PageController::class, 'contact'])->name('public.pages.contact');

Route::get('/hubungi-kami', [PageController::class, 'hubungiKami'])->name('public.hubungi-kami');

Route::get('/properti', [ListingController::class, 'index'])->name('public.listings.index');
Route::get('/properti/{listing}', [ListingController::class, 'show'])->name('public.listings.show');

Route::get('/blog', [BlogController::class, 'index'])->name('public.blog.index');
Route::get('/blog/{post}', [BlogController::class, 'show'])->name('public.blog.show');

Route::get('/layanan', [LayananController::class, 'index'])->name('public.layanan.index');

Route::get('/sitemap.xml', SitemapController::class)->name('public.sitemap');

Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('public.robots');

if (! app()->environment('production')) {
    Route::view('/design-system', 'design-system')->name('design-system');
}

Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/wilayah', [WilayahController::class, 'wilayah'])
        ->middleware('throttle:120,1')
        ->name('admin.wilayah');

    Route::get('/admin/alamat', [WilayahController::class, 'alamat'])
        ->middleware('throttle:60,1')
        ->name('admin.alamat');

    // Mutating routes. The screens that submit them live in the
    // password.confirm group below.
    Route::resource('/admin/listings', App\Http\Controllers\Admin\ListingController::class)
        ->except(['show', 'index', 'create', 'edit'])
        ->names('admin.listings');

    Route::delete('/admin/listings/{listing}/foto/{image}', [App\Http\Controllers\Admin\ListingController::class, 'destroyPhoto'])
        ->name('admin.listings.photos.destroy');

    Route::resource('/admin/blog', BlogPostController::class)
        ->except(['show', 'index', 'create', 'edit'])
        ->parameters(['blog' => 'post'])
        ->names('admin.blog');

    Route::post('/admin/pengaturan-listing/developer', [ListingSettingController::class, 'storeDeveloper'])
        ->name('admin.listing-settings.developers.store');
    Route::post('/admin/pengaturan-listing/opsi', [ListingSettingController::class, 'storeOption'])
        ->name('admin.listing-settings.options.store');
    Route::put('/admin/pengaturan-listing/opsi/{option}', [ListingSettingController::class, 'updateOption'])
        ->name('admin.listing-settings.options.update');
    Route::delete('/admin/pengaturan-listing/opsi/{option}', [ListingSettingController::class, 'destroyOption'])
        ->name('admin.listing-settings.options.destroy');

    Route::put('/admin/developers/{developer}', [DeveloperController::class, 'update'])
        ->name('admin.developers.update');
    Route::delete('/admin/pengaturan-listing/developer/{developer}', [ListingSettingController::class, 'destroyDeveloper'])
        ->name('admin.listing-settings.developers.destroy');

    Route::resource('/admin/agents', AgentController::class)
        ->except(['show', 'index', 'create', 'edit'])
        ->names('admin.agents');

    Route::resource('/admin/layanan', ServiceController::class)
        ->except(['show', 'index', 'create', 'edit'])
        ->parameters(['layanan' => 'service'])
        ->names('admin.services');

    Route::post('/admin/layanan/{service}/{direction}', [ServiceController::class, 'move'])
        ->whereIn('direction', ['up', 'down'])
        ->name('admin.services.move');

    Route::resource('/admin/users', UserController::class)
        ->except(['show', 'index', 'create', 'edit'])
        ->names('admin.users');

    Route::put('/admin/pages/{page}', [PageContentController::class, 'update'])->name('admin.pages.update');
    Route::put('/admin/settings', [SiteSettingController::class, 'update'])->name('admin.settings.update');
    Route::put('/admin/template-whatsapp', [WhatsAppTemplateController::class, 'update'])
        ->name('admin.whatsapp-templates.update');

    Route::get('/admin/audit', [AuditLogController::class, 'index'])->name('admin.audit.index');

    // Screens that host a destructive button ask for the password again.
    // Confirming here keeps DELETE requests replayable: Laravel's
    // password.confirm redirects with a GET after confirmation.
    Route::middleware('password.confirm')->group(function () {
        Route::get('/admin/listings', [App\Http\Controllers\Admin\ListingController::class, 'index'])->name('admin.listings.index');
        Route::get('/admin/listings/create', [App\Http\Controllers\Admin\ListingController::class, 'create'])->name('admin.listings.create');
        Route::get('/admin/listings/{listing}/edit', [App\Http\Controllers\Admin\ListingController::class, 'edit'])->name('admin.listings.edit');

        Route::get('/admin/blog', [BlogPostController::class, 'index'])->name('admin.blog.index');
        Route::get('/admin/blog/create', [BlogPostController::class, 'create'])->name('admin.blog.create');
        Route::get('/admin/blog/{post}/edit', [BlogPostController::class, 'edit'])->name('admin.blog.edit');

        Route::get('/admin/pengaturan-listing', [ListingSettingController::class, 'index'])
            ->name('admin.listing-settings.index');
        Route::get('/admin/developers/{developer}/edit', [DeveloperController::class, 'edit'])
            ->name('admin.developers.edit');

        Route::get('/admin/agents', [AgentController::class, 'index'])->name('admin.agents.index');
        Route::get('/admin/agents/create', [AgentController::class, 'create'])->name('admin.agents.create');
        Route::get('/admin/agents/{agent}/edit', [AgentController::class, 'edit'])->name('admin.agents.edit');

        Route::get('/admin/layanan', [ServiceController::class, 'index'])->name('admin.services.index');
        Route::get('/admin/layanan/create', [ServiceController::class, 'create'])->name('admin.services.create');
        Route::get('/admin/layanan/{service}/edit', [ServiceController::class, 'edit'])->name('admin.services.edit');

        Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');

        Route::get('/admin/pages', [PageContentController::class, 'index'])->name('admin.pages.index');
        Route::get('/admin/pages/{page}/edit', [PageContentController::class, 'edit'])->name('admin.pages.edit');

        Route::get('/admin/settings', [SiteSettingController::class, 'edit'])->name('admin.settings.edit');

        Route::get('/admin/template-whatsapp', [WhatsAppTemplateController::class, 'edit'])
            ->name('admin.whatsapp-templates.edit');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->middleware('password.confirm')
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
