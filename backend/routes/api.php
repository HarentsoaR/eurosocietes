<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use Illuminate\Support\Facades\Route;
Route::prefix('v1')->group(function () {
    Route::get('ping', fn () => response()->json(['message' => 'pong']))
        ->name('api.ping');

    Route::post('register', [AuthController::class, 'register'])
        ->middleware('throttle:auth.register')
        ->name('api.register');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:auth.login')
        ->name('api.login');

    Route::post('password/forgot', [PasswordResetController::class, 'forgotPassword'])
        ->middleware('throttle:auth.forgot')
        ->name('api.password.forgot');

    Route::post('password/reset', [PasswordResetController::class, 'reset'])
        ->middleware('throttle:auth.reset')
        ->name('api.password.reset');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('api.logout');
        Route::get('me', [AuthController::class, 'me'])->name('api.me');

        Route::get('admin/ping', fn () => response()->json(['message' => 'ok']))
            ->middleware('role:admin')
            ->name('api.admin.ping');
    });

    /*
    |--------------------------------------------------------------------------
    | Geographic referential & NAF (public reads)
    |--------------------------------------------------------------------------
    */
    Route::get('naf-activites', [ReferentielController::class, 'nafActivites'])->name('api.naf-activites');
    Route::get('naf-activites/{naf_activite}', [ReferentielController::class, 'nafActivite'])->name('api.naf-activites.show');
    Route::get('specialites', [ReferentielController::class, 'specialites'])->name('api.specialites');
    Route::get('pays', [ReferentielController::class, 'pays'])->name('api.pays');
    Route::get('pays/{pays}', [ReferentielController::class, 'regions'])->name('api.pays.regions');
    Route::get('regions/{region}/departements', [ReferentielController::class, 'departements'])->name('api.regions.departements');
    Route::get('departements/{departement}/villes', [ReferentielController::class, 'villes'])->name('api.departements.villes');
    Route::get('villes', [ReferentielController::class, 'villesSearch'])->name('api.villes');
    Route::get('villes/{ville}', [ReferentielController::class, 'ville'])->name('api.villes.show');
    Route::get('villes/{ville}/code-postaux', [ReferentielController::class, 'codePostaux'])->name('api.villes.code-postaux');

    /*
    |--------------------------------------------------------------------------
    | Entreprise & fiche API (public reads + admin writes)
    |--------------------------------------------------------------------------
    */
    Route::get('entreprises', [EntrepriseController::class, 'index'])->name('api.entreprises.index');
    Route::get('entreprises/{entreprise}', [EntrepriseController::class, 'show'])->name('api.entreprises.show');
    Route::get('entreprises/{entreprise}/etablissements', [EntrepriseController::class, 'etablissements'])->name('api.entreprises.etablissements');
    Route::get('entreprises/{entreprise}/dirigeants', [EntrepriseController::class, 'dirigeants'])->name('api.entreprises.dirigeants');
    Route::get('entreprises/{entreprise}/sections', [EntrepriseController::class, 'sections'])->name('api.entreprises.sections');

    /*
    |--------------------------------------------------------------------------
    | Entreprise admin writes (Sanctum + permission middleware)
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:companies.create')->post('entreprises', [EntrepriseController::class, 'store'])->name('api.entreprises.store');
    Route::middleware('permission:companies.update')->put('entreprises/{entreprise}', [EntrepriseController::class, 'update'])->name('api.entreprises.update');
    Route::middleware('permission:companies.delete')->delete('entreprises/{entreprise}', [EntrepriseController::class, 'destroy'])->name('api.entreprises.destroy');

    /*
    |--------------------------------------------------------------------------
    | Editorial & monetisation content API
    |--------------------------------------------------------------------------
    */
    Route::get('contenus-ia', [ContenuController::class, 'contenusIa'])->name('api.contenus-ia.index');
    Route::post('contenus-ia', [ContenuController::class, 'storeContenuIa'])->name('api.contenus-ia.store');
    Route::put('contenus-ia/{contenu_ia}', [ContenuController::class, 'updateContenuIa'])->name('api.contenus-ia.update');
    Route::get('faq', [ContenuController::class, 'faqs'])->name('api.faq.index');
    Route::get('documents', [ContenuController::class, 'documents'])->name('api.documents.index');
    Route::get('entreprises/{entreprise}/passeport', [MonetisationController::class, 'passeport'])->name('api.entreprises.passeport');
    Route::get('entreprises/{entreprise}/abonnement', [MonetisationController::class, 'abonnement'])->name('api.entreprises.abonnement');
    Route::get('publicites', [MonetisationController::class, 'publicites'])->name('api.publicites.index');

    /*
    |--------------------------------------------------------------------------
    | Search & analytics API
    |--------------------------------------------------------------------------
    */
    Route::get('search', [SearchController::class, 'search'])->name('api.search');
    Route::post('recherches', [SearchController::class, 'log'])->name('api.recherches.store');

    /*
    |--------------------------------------------------------------------------
    | Statistiques (admin only)
    |--------------------------------------------------------------------------
    */
    Route::middleware('permission:statistiques.view')->get('statistiques', [StatistiquesController::class, 'index'])->name('api.statistiques.index');
});
