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

    $exceptShow = ['except' => [ 'show' ] ];

    Route::get('/dashboard', 'Backend\DashboardController@index')->name('home');

    Route::resource('/articles', 'Backend\ArticleController');
    Route::resource('/developers', 'Backend\DeveloperController', $exceptShow);
    Route::resource('/games', 'Backend\GameController', $exceptShow);
    Route::resource('/genres', 'Backend\GenreController', $exceptShow);
    Route::resource('/platforms', 'Backend\PlatformController', $exceptShow);
    Route::resource('/publishers', 'Backend\PublisherController', $exceptShow);
    Route::resource('/rssitems', 'Backend\RSSItemController', $exceptShow);
    Route::resource('/rsswebsites', 'Backend\RSSWebsiteController', $exceptShow);
    Route::resource('/users', 'Backend\UserController', $exceptShow);
    Route::resource('/pages', 'Backend\PageController')->parameters([
        'pages' => 'admin_pages'
    ]);

    Route::get('games/recently-in-rss', 'Backend\GameController@recentlyInRSS');
    Route::get('games/recently-in-rss/coupling', 'Backend\GameController@recentlyInRSSCoupling');
    Route::get('games/find-publisher/{game}', 'Backend\GameController@tryToGetThePublisher');
    Route::get('games/find-developer/{game}', 'Backend\GameController@findDeveloper');
    Route::get('games/create/{title}', 'Backend\GameController@create');
    Route::get('/news', 'Backend\ArticleController@index');
    Route::get('publishers/create/{title}', 'Backend\PublisherController@create');
    Route::get('rssitems/find-keywords', 'Backend\RSSItemController@findKeywords');
    Route::get('rssitems/suggest-game-title', 'RSSCrawlerController@suggestGameTitle');
});

// Sitemap
Route::get('/sitemap.xml', 'SitemapController@index');
Route::get('/sitemap', 'SitemapController@index');
Route::get('/sitemap/articles', 'SitemapController@articles');
Route::get('/sitemap/platforms', 'SitemapController@platforms');
Route::get('/sitemap/games', 'SitemapController@games');
Route::get('/sitemap/pages', 'SitemapController@pages');
Route::get('/rss', 'SitemapController@rss');

// Articles
Route::get('/news', 'ArticleController@index');
Route::get('/article/{article}', 'ArticleController@show');

// Publishers
Route::get('/publishers', 'PublisherController@index');
Route::get('/publishers/{publisher}', 'PublisherController@show');

// Developers
Route::get('/developers', 'DeveloperController@index');
Route::get('/developers/{developer}', 'DeveloperController@show');

// Platforms
Route::get('/platforms', 'PlatformController@index');
Route::get('/platforms/{platform}', 'PlatformController@show');

// Crawler
Route::get('/crawler/crawl', 'RSSCrawlerController@crawl');

// Games
Route::get('/games/list', 'GameController@listed');
Route::get('/games/upcoming', 'GameController@upcoming');
Route::resource('/games', 'GameController');

// Authentication
Auth::routes();
Route::get('/validate', 'Auth\ActivateController@index');

// Other pages
Route::get('/', 'PageController@home');

// Very last route for catching all pages
Route::get('/{page}', 'PageController@show');
