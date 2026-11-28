<?php
use app\api\middleware\ApiChannel;
use app\api\middleware\ApiCheckToken;
use app\api\middleware\ApiLog;
use think\facade\Route;

// Login is optional: logged-in members get their own sid for order attribution.
Route::group('baijia_tools', function () {
    Route::get('native/kfc', 'addon\baijia_tools\app\api\controller\NativeCatalog@kfc');
    Route::get('native/movies', 'addon\baijia_tools\app\api\controller\NativeCatalog@movies');
    Route::get('dining/catalog', 'addon\baijia_tools\app\api\controller\Dining@catalog');
    Route::get('meituan/products', 'addon\baijia_tools\app\api\controller\Meituan@products');
    Route::get('meituan/link', 'addon\baijia_tools\app\api\controller\Meituan@link');
    Route::get('jtk/acts', 'addon\baijia_tools\app\api\controller\Jtk@acts');
    Route::get('jtk/link', 'addon\baijia_tools\app\api\controller\Jtk@link');
})->middleware(ApiChannel::class)
    ->middleware(ApiCheckToken::class, false)
    ->middleware(ApiLog::class);

// Member's own rebate orders: login required.
Route::group('baijia_tools', function () {
    Route::get('mayi/launch', 'addon\baijia_tools\app\api\controller\Mayi@launch');
    Route::get('mayi/orders', 'addon\baijia_tools\app\api\controller\Mayi@orders');
    Route::get('cps/orders', 'addon\baijia_tools\app\api\controller\CpsOrder@orders');
    Route::get('cps/summary', 'addon\baijia_tools\app\api\controller\CpsOrder@summary');
    Route::get('distribution/orders', 'addon\baijia_tools\app\api\controller\Distribution@orders');
    Route::get('distribution/fans', 'addon\baijia_tools\app\api\controller\Distribution@fans');
    Route::get('distribution/summary', 'addon\baijia_tools\app\api\controller\Distribution@summary');
    Route::get('promotion/fans', 'addon\baijia_tools\app\api\controller\CpsOrder@fans');
})->middleware(ApiChannel::class)
    ->middleware(ApiCheckToken::class, true)
    ->middleware(ApiLog::class);
