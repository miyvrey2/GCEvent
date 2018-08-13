<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Backend
Route::group(['prefix' => 'admin'], function() {
    Route::get('/dashboard', 'Backend\DashboardController@index')->name('home');

});

// Sitemap
Route::get('/sitemap.xml', 'SitemapController@index');
Route::get('/sitemap', 'SitemapController@index');
Route::get('/sitemap/articles', 'SitemapController@articles');
Route::get('/sitemap/consoles', 'SitemapController@consoles');
Route::get('/sitemap/games', 'SitemapController@games');
Route::get('/sitemap/pages', 'SitemapController@pages');

// Articles
Route::get('/news', 'ArticleController@index');
Route::get('/article/{article}', 'ArticleController@show');

// Publishers
Route::get('/publishers', 'PublisherController@index');
Route::get('/publishers/{publisher}', 'PublisherController@show');

// Consoles
Route::get('/consoles', 'ConsoleController@index');
Route::get('/consoles/{console}', 'ConsoleController@show');

// Crawler
Route::get('/crawler', 'RSSCrawlerController@index');
Route::get('/crawler/removeDuplicates', 'RSSCrawlerController@removeDuplicates');
Route::get('/crawler/removeOldNews', 'RSSCrawlerController@removeOldNews');
Route::get('/crawler/crawl', 'RSSCrawlerController@crawl');
Route::get('/crawler/gametitles', 'RSSCrawlerController@getGameTitles');

// Games
Route::get('/games/list', 'GameController@listed');
Route::resource('/games', 'GameController');

// Authentication
Auth::routes();
Route::get('/validate', 'Auth\ActivateController@index');
// Other pages
Route::get('/', 'PageController@home');
Route::get('/pages', 'PageController@index');
// Very last route for catching all pages
Route::get('/{page}', 'PageController@show');
