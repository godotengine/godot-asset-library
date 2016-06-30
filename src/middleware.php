<?php

if(isset($frontend) && $frontend) {
  $app->add(function ($request, $response, $next) {
    $cookie = $this->cookies['requestCookies']::get($request, 'token');
    $body = $request->getParsedBody();
    if($cookie->getValue() !== null && !isset($body['token'])) {
      $body['token'] = $cookie->getValue();
    }


    $response = $next($request, $response);
    $response->getBody()->rewind();
    $result = json_decode($response->getBody()->getContents(), true);


    $static_routes = [
      '/login' => true,
      '/register' => true,
    ];
    $queryUri = false;

    $route = $request->getAttribute('route');
    $path = $request->getUri()->getPath();

    if($route) {
      $queryUri = $route->getPattern();
    } else if(isset($static_routes['/' . $path])) {
      $queryUri = '/' . $path;
    }

    if($queryUri === false) {
      return $response;
    }
    $queryUri = $request->getMethod() . ' ' . $queryUri;

    if(isset($result['authenticated'])) {
      $result['url'] = 'asset';
    }


    if(isset($result['url'])) {
      $response = new \Slim\Http\Response(303);
      $response = $response->withHeader('Location', dirname($request->getUri()->getBasePath()) . '/frontend/' . $result['url']);
    } else {
      $template_names = [
        //'/configure' => 'configure',
        'GET /asset' => 'assets',
        //'/asset/{id}' => 'asset',
        //'/asset/edit/{id}' => 'asset_edit',
        //'/register' => 'registered',
        'GET /login' => 'login',
        'GET /register' => 'register',
      ];

      if(isset($template_names[$queryUri])) {
        $response = new \Slim\Http\Response();
        $errorResponse = new \Slim\Http\Response();
        $params = [
          'data' => $result,
          'basepath' => dirname($request->getUri()->getBasePath()) . '/frontend',
          'path' => $path,
          'params' => $request->getQueryParams(),
          //'body' => $request->getParsedBody(),
        ];

        if(isset($body['token'])) {
          $token = $this->tokens->validate($body['token']);
          $error = $this->utils->get_user_from_token_data(false, $errorResponse, $token, $user);
          if(!$error) {
            $params['user'] = $user;
          }
        }

        $response = $this->renderer->render($response, $template_names[$queryUri] . '.phtml', $params);
      }
    }

    if(isset($result['token'])) {
      $response = $this->cookies['responseCookies']::set($response, $this->cookies['setCookie']('token')
        ->withValue($result['token'])
        ->withDomain($_SERVER['HTTP_HOST'])
      );
    }
    return $response;
  });
}
