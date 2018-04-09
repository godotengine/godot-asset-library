<?php

function authMiddleware($level = '', $allow_owner_of = '')
{
    static $results_cache = [];
    $cache_key = $level . '.' . $allow_owner_of;
    if (!isset($results_cache[$cache_key])) {
        $results_cache[$cache_key] = function ($request, $response, $next) use ($level, $allow_owner_of) {
            $container = $this;

            $body = $request->getParsedBody();
            $error = $container->utils->ensureLoggedIn(false, $response, $body, $user);
            if ($level !== '' && $allow_owner_of === '') {
                $error = $container->utils->errorResponseIfNotUserHasLevel($error, $response, $user, $level);
            } elseif ($level !== '' && $allow_owner_of !== '') {
                $route = $request->getAttribute('route');
                $error = $container->utils->errorResponseIfNotOwnerOrLevel($error, $response, $user, $route->getArgument('id'), $level, $allow_owner_of);
            }
            if (!$error) {
                $request = $request->withAttribute('user', $user);
                $response = $next($request, $response);
            }
            return $response;
        };
    }
    return $results_cache[$cache_key];
}



$app->get('/configure', 'AuthController:configure');

$app->get('/asset', 'AssetController:list');
$app->get('/asset/{id:[0-9]+}', 'AssetController:getOne');
$app->post('/asset/{id:[0-9]+}/delete', 'AssetController:softDelete')->add(authMiddleware('moderator', 'asset'));
$app->post('/asset/{id:[0-9]+}/undelete', 'AssetController:softUndelete')->add(authMiddleware('moderator', 'asset'));
$app->post('/asset/{id:[0-9]+}/support_level', 'AssetController:changeSupportLevel')->add(authMiddleware('moderator'));

$app->post('/asset', 'AssetEditController:createForNewAsset')->add(authMiddleware());
$app->post('/asset/{id:[0-9]+}', 'AssetEditController:createForExistingAsset')->add(authMiddleware('editor', 'asset'));

$app->get('/asset/edit', 'AssetEditController:list');
$app->get('/asset/edit/{id:[0-9]+}', 'AssetEditController:getOne');
$app->post('/asset/edit/{id:[0-9]+}', 'AssetEditController:update')->add(authMiddleware('editor', 'asset_edit'));
$app->post('/asset/edit/{id:[0-9]+}/accept', 'AssetEditController:accept')->add(authMiddleware('moderator'));
$app->post('/asset/edit/{id:[0-9]+}/reject', 'AssetEditController:reject')->add(authMiddleware('moderator'));
$app->post('/asset/edit/{id:[0-9]+}/review', 'AssetEditController:review')->add(authMiddleware('moderator'));

$app->post('/change_password', 'AuthController:changePassword')->add(authMiddleware());
$app->post('/forgot_password', 'AuthController:sendResetPasswordEmail');
$app->post('/login', 'AuthController:login');
$app->post('/logout', 'AuthController:logout')->add(authMiddleware());
$app->post('/register', 'AuthController:register');
$app->post('/reset_password', 'AuthController:resetPassword');
$app->post('/user/feed', 'UserController:getFeed')->add(authMiddleware());

if (FRONTEND) {
    $app->get('/asset/{id:[0-9]+}/edit', 'AssetController:getOne');
    $app->get('/asset/edit/{id:[0-9]+}/edit', 'AssetEditController:getOne');
    $app->get('/logout', 'AuthController:logout')->add(authMiddleware());
    $app->get('/reset_password', 'AuthController:temporaryResetLogin');
    $app->get('/user/feed', 'UserController:getFeed')->add(authMiddleware());
}
