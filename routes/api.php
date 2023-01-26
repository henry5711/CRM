<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix'     => 'users',
], function () {
Route::get('/generate/qr/code/{id}', [App\Http\Controllers\Api\Users\ShowController::class, 'generatorQrClient'])
->name('api.v1.users.generatorQrClient'); });

Route::group([
    'prefix'     => 'images',
], function () {
    Route::get('/selectSystemImageType', [App\Http\Controllers\Api\Images\IndexController::class, 'selectSystemImageType'])
        ->name('api.v1.images.index.selectSystemImageType');

    Route::get('/filter/images', [App\Http\Controllers\Api\Images\IndexController::class, 'filterImagen'])
        ->name('api.v1.images.index.filterImagen');

    Route::get('/getSystemImageProfile', [App\Http\Controllers\Api\Images\IndexController::class, 'getSystemImageProfile'])
        ->name('api.v1.images.index.getSystemImageProfile')->middleware('cognitoAuth');

    Route::get('/showSystemImageProfile/{id}', [App\Http\Controllers\Api\Images\ShowController::class, 'showSystemImageProfile'])
        ->name('api.v1.images.show.showSystemImageProfile')->middleware('cognitoAuth');

    Route::post('/indexSystemImageType', [App\Http\Controllers\Api\Images\IndexController::class, 'indexSystemImageType'])
        ->name('api.v1.images.index.indexSystemImageType')->middleware('cognitoAuth');

    Route::post('/registerSystemImageType', [App\Http\Controllers\Api\Images\RegisterController::class, 'registerSystemImageType'])
        ->name('api.v1.images.register.registerSystemImageType')->middleware('cognitoAuth');

    Route::post('/UpdateImagen/{id}', [App\Http\Controllers\Api\Images\RegisterController::class, 'Update'])
        ->name('api.v1.images.register.registerSystemImageType')->middleware('cognitoAuth');

    Route::post('/registerImage', [App\Http\Controllers\Api\Images\RegisterController::class, 'registerImage'])
        ->name('api.v1.images.register.registerImage')->middleware('cognitoAuth');

    Route::delete('/deleteImageSystem/{id_image}', [App\Http\Controllers\Api\Images\DeleteController::class, 'deleteImageSystem'])
        ->name('api.v1.images.delete.deleteImageSystem')->middleware('cognitoAuth');

    Route::delete('/deleteImageSystem/file/{id}', [App\Http\Controllers\Api\Images\DeleteController::class, 'deleteImageSystemAll'])
        ->name('api.v1.images.delete.deleteImageSystemall')->middleware('cognitoAuth');
});


Route::group([
    'prefix'     => 'origin'
], function () {
    Route::get('', [App\Http\Controllers\Api\Origin\OriginController::class, 'index'])
        ->name('api.v1.origin.index.index');

    Route::delete('delete/{id}', [App\Http\Controllers\Api\Origin\OriginController::class, 'destroy'])
        ->name('api.v1.currencyCountry.currencyCountry.index');

    Route::get('show/{id}', [App\Http\Controllers\Api\Origin\OriginController::class, 'show'])
        ->name('api.v1.currencyCountry.show.show');

    Route::get('filter/origin', [App\Http\Controllers\Api\Origin\OriginController::class, 'filter'])
        ->name('api.v1.currencyCountry.filter');

    Route::post('{id}', [App\Http\Controllers\Api\Origin\OriginController::class, 'update'])
        ->name('api.v1.currencyCountry.update.updateA')
        ->middleware('cognitoAuth');

    Route::post('', [App\Http\Controllers\Api\Origin\OriginController::class, 'store'])
        ->name('api.v1.currencyCountry.store.store')
        ->middleware('cognitoAuth');
});

Route::group([
    'prefix'     => 'client'
], function () {
    Route::get('', [App\Http\Controllers\Api\Client\ClientController::class, 'index'])
        ->name('api.v1.client.index.index');

    Route::delete('delete/{id}', [App\Http\Controllers\Api\Client\ClientController::class, 'destroy'])
        ->name('api.v1.client.currencyCountry.index');

    Route::get('show/{id}', [App\Http\Controllers\Api\Client\ClientController::class, 'show'])
        ->name('api.v1.client.show.show');

    Route::get('filter/client/result', [App\Http\Controllers\Api\Client\ClientController::class, 'filter'])
        ->name('api.v1.client.filter');

    Route::post('{id}', [App\Http\Controllers\Api\Client\ClientController::class, 'update'])
        ->name('api.v1.client.update.updateA')
        ->middleware('cognitoAuth');

    Route::post('', [App\Http\Controllers\Api\Client\ClientController::class, 'store'])
        ->name('api.v1.client.store.store')
        ->middleware('cognitoAuth');

    Route::get('status/filter/client', [App\Http\Controllers\Api\Client\ClientController::class, 'filterStatusClient'])
        ->name('api.v1.client.filter.status');

    Route::post('observe/post/client', [App\Http\Controllers\Api\Client\ClientController::class, 'addobserve'])
        ->name('api.v1.tak.addobserve.addobserve')
        ->middleware('cognitoAuth');

    Route::post('observe/update/client/{id}', [App\Http\Controllers\Api\Client\ClientController::class, 'updateObserve'])
        ->name('api.v1.client.updateObserve.updateObserve')
        ->middleware('cognitoAuth');

    Route::delete('delete/client/observe/{id}', [App\Http\Controllers\Api\Client\ClientController::class, 'destroyObserve'])
        ->name('api.v1.client.destroyObserve.destroyObserve');

    Route::post('migrate/import', [App\Http\Controllers\Api\Client\ClientController::class, 'importExcel'])
        ->name('api.v1.client.importExcel.importExcel')
        ->middleware('cognitoAuth');
});

Route::group([
    'prefix'     => 'type/typification'
], function () {
    Route::get('', [App\Http\Controllers\Api\TypeTypification\TypeTypificationController::class, 'index'])
        ->name('api.v1.origin.index.index');

    Route::delete('delete/{id}', [App\Http\Controllers\Api\TypeTypification\TypeTypificationController::class, 'destroy'])
        ->name('api.v1.currencyCountry.currencyCountry.index');

    Route::get('show/{id}', [App\Http\Controllers\Api\TypeTypification\TypeTypificationController::class, 'show'])
        ->name('api.v1.currencyCountry.show.show');

    Route::get('filter/origin', [App\Http\Controllers\Api\TypeTypification\TypeTypificationController::class, 'filter'])
        ->name('api.v1.currencyCountry.filter');

    Route::post('{id}', [App\Http\Controllers\Api\TypeTypification\TypeTypificationController::class, 'update'])
        ->name('api.v1.currencyCountry.update.updateA')
        ->middleware('cognitoAuth');

    Route::post('', [App\Http\Controllers\Api\TypeTypification\TypeTypificationController::class, 'store'])
        ->name('api.v1.currencyCountry.store.store')
        ->middleware('cognitoAuth');
});


Route::group([
    'prefix'     => 'typification'
], function () {
    Route::get('', [App\Http\Controllers\Api\Typification\TypificationController::class, 'index'])
        ->name('api.v1.origin.index.index');

    Route::delete('delete/{id}', [App\Http\Controllers\Api\Typification\TypificationController::class, 'destroy'])
        ->name('api.v1.currencyCountry.currencyCountry.index');

    Route::get('show/{id}', [App\Http\Controllers\Api\Typification\TypificationController::class, 'show'])
        ->name('api.v1.currencyCountry.show.show');

    Route::get('filter/origin', [App\Http\Controllers\Api\Typification\TypificationController::class, 'filter'])
        ->name('api.v1.currencyCountry.filter');

    Route::post('{id}', [App\Http\Controllers\Api\Typification\TypificationController::class, 'update'])
        ->name('api.v1.currencyCountry.update.updateA')
        ->middleware('cognitoAuth');

    Route::post('', [App\Http\Controllers\Api\Typification\TypificationController::class, 'store'])
        ->name('api.v1.currencyCountry.store.store')
        ->middleware('cognitoAuth');
});


Route::group([
    'prefix'     => 'tak'
], function () {
    Route::get('', [App\Http\Controllers\Api\Tak\TakController::class, 'index'])
        ->name('api.v1.client.index.index');

    Route::delete('delete/{id}', [App\Http\Controllers\Api\Tak\TakController::class, 'destroy'])
        ->name('api.v1.client.currencyCountry.index');

    Route::get('show/{id}', [App\Http\Controllers\Api\Tak\TakController::class, 'show'])
        ->name('api.v1.client.show.show');


    Route::get('filter/agent/tak/{user_id}', [App\Http\Controllers\Api\Tak\TakController::class, 'listAgetsTaks'])
        ->name('api.v1.takController.listAgetsTaks.listAgetsTaks');

    Route::get('filter/tak', [App\Http\Controllers\Api\Tak\TakController::class, 'filter'])
        ->name('api.v1.client.filter');

    Route::post('{id}', [App\Http\Controllers\Api\Tak\TakController::class, 'update'])
        ->name('api.v1.client.update.updateA')
        ->middleware('cognitoAuth');

    Route::post('', [App\Http\Controllers\Api\Tak\TakController::class, 'store'])
        ->name('api.v1.client.store.store')
        ->middleware('cognitoAuth');

    Route::post('label/post/tak/{id}', [App\Http\Controllers\Api\Tak\TakController::class, 'relationLabels'])
        ->name('api.v1.tak.relationLabels.relationLabels')
        ->middleware('cognitoAuth');

    Route::post('file/tak/post/{id}', [App\Http\Controllers\Api\Tak\TakController::class, 'addFile'])
        ->name('api.v1.tak.addFile.addFile')
        ->middleware('cognitoAuth');
});


Route::group([
    'prefix'     => 'comment/tak'
], function () {
    Route::get('', [App\Http\Controllers\Api\TakComment\TakCommentController::class, 'index'])
        ->name('api.v1.client.index.index');

    Route::delete('delete/{id}', [App\Http\Controllers\Api\TakComment\TakCommentController::class, 'destroy'])
        ->name('api.v1.client.currencyCountry.index');

    Route::get('show/{id}', [App\Http\Controllers\Api\TakComment\TakCommentController::class, 'show'])
        ->name('api.v1.client.show.show');

    Route::get('filter/tak', [App\Http\Controllers\Api\TakComment\TakCommentController::class, 'filter'])
        ->name('api.v1.client.filter');

    Route::post('{id}', [App\Http\Controllers\Api\TakComment\TakCommentController::class, 'update'])
        ->name('api.v1.client.update.updateA')
        ->middleware('cognitoAuth');

    Route::post('', [App\Http\Controllers\Api\TakComment\TakCommentController::class, 'store'])
        ->name('api.v1.client.store.store')
        ->middleware('cognitoAuth');
});


Route::group([
    'prefix'     => 'faq'
], function () {

    Route::group([
        'prefix' => 'category'
    ],  function () {
        Route::get('', [App\Http\Controllers\Api\Faq\CategoryFaqController::class, 'fetch'])
            ->name('api.v1.categori.faq.fetch');
        Route::get('index', [App\Http\Controllers\Api\Faq\CategoryFaqController::class, 'index'])
            ->name('api.v1.categori.faq.index');
        Route::delete('delete/{id}', [App\Http\Controllers\Api\Faq\CategoryFaqController::class, 'destroy'])
            ->name('api.v1.categori.faq.destroy');
        Route::post('store', [App\Http\Controllers\Api\Faq\CategoryFaqController::class, 'store'])
            ->name('api.v1.categori.faq.store');
        Route::post('update/{id}', [App\Http\Controllers\Api\Faq\CategoryFaqController::class, 'update'])
            ->name('api.v1.categori.faq.update');
    });

    Route::group([
        'prefix' => 'data'
    ],  function () {
        Route::get('', [App\Http\Controllers\Api\Faq\DataFaqController::class, 'fetch'])
            ->name('api.v1.data.faq.fetch');
        Route::get('index', [App\Http\Controllers\Api\Faq\DataFaqController::class, 'index'])
            ->name('api.v1.data.faq.index');
        Route::delete('delete/{id}', [App\Http\Controllers\Api\Faq\DataFaqController::class, 'destroy'])
            ->name('api.v1.data.faq.destroy');
        Route::post('store', [App\Http\Controllers\Api\Faq\DataFaqController::class, 'store'])
            ->name('api.v1.data.faq.store');
        Route::post('update/{id}', [App\Http\Controllers\Api\Faq\DataFaqController::class, 'update'])
            ->name('api.v1.data.faq.update');
    });

    Route::group([
        'prefix' => 'detail'
    ],  function () {
        Route::get('', [App\Http\Controllers\Api\Faq\DetailFaqController::class, 'fetch'])
            ->name('api.v1.detail.faq.fetch');
    });
});

Route::group([
    'prefix'     => 'label'
], function () {

    Route::group([
        'prefix' => 'taks'
    ],  function () {
        Route::get('', [App\Http\Controllers\Api\LabelTaks\LabelTaksController::class, 'fetch'])
            ->name('api.v1.label.taks.fetch');
        Route::get('index', [App\Http\Controllers\Api\LabelTaks\LabelTaksController::class, 'index'])
            ->name('api.v1.label.taks.index');
        Route::delete('delete/{id}', [App\Http\Controllers\Api\LabelTaks\LabelTaksController::class, 'destroy'])
            ->name('api.v1.label.taks.destroy');
        Route::post('store', [App\Http\Controllers\Api\LabelTaks\LabelTaksController::class, 'store'])
            ->name('api.v1.label.taks.store');
        Route::post('update/{id}', [App\Http\Controllers\Api\LabelTaks\LabelTaksController::class, 'update'])
            ->name('api.v1.label.taks.update');
    });
});

