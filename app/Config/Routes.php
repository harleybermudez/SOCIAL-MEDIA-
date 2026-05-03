<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// HOME → redirect to login
$routes->get('/', function () {
    return redirect()->to('/login');
});

// AUTH
$routes->get('/login', 'Auth::login');
$routes->post('/login', 'Auth::auth');

$routes->get('/register', 'Auth::register');
$routes->post('/register', 'Auth::store');

$routes->get('/logout', 'Auth::logout');
$routes->get('/reset-password', 'Auth::resetPassword');
$routes->post('/reset-password-submit', 'Auth::resetPasswordSubmit');

// FEED
$routes->get('/feed', 'Post::index');
$routes->get('/quickie', 'Quickie::index');

// PROFILE
$routes->get('/profile/(:num)', 'Profile::index/$1');
$routes->get('/profile/edit', 'Profile::edit');
$routes->post('/profile/update', 'Profile::update');

// FRIEND REQ
$routes->get('/friends', 'Friend::friends');
$routes->post('/friend/request', 'Friend::request');
$routes->post('/friend/accept', 'Friend::accept');
$routes->post('/friend/reject', 'Friend::reject');
$routes->post('/friend/remove', 'Friend::remove');
$routes->get('/api/friends/(:num)', 'Friend::getFriends/$1');
$routes->post('/api/friend/remove', 'Friend::removeFriendApi');
$routes->post('/api/friend/accept', 'Friend::acceptRequest');
$routes->post('/api/friend/reject', 'Friend::rejectRequest');

// POSTS
$routes->get('/post/create', 'Post::create');
$routes->post('/post/store', 'Post::store');

// LIKE
$routes->post('/like/toggle', 'Like::toggle');

// TEST
$routes->get('/test', 'Test::index');

// COMMENT
$routes->post('/comment/store', 'Comment::store');
$routes->post('/comment/like/toggle', 'Comment::toggleLike');
$routes->get('/api/comments/(:num)', 'Comment::fetch/$1');

// SHARE
$routes->post('/share/repost', 'Share::repost');

// FRIEND
$routes->get('/users', 'Friend::index');
