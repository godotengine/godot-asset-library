<?php

if(isset($frontend) && $frontend) {
  $app->get('/', function ($request, $response) {
    return $response->withJson(['url' => 'asset']);
  });

  $app->add(function ($request, $response, $next) {
    $cookie = $this->cookies['requestCookies']::get($request, 'token');
    $body = $request->getParsedBody();
    if($cookie->getValue() !== null && !isset($body['token'])) {
      $cookieValue = (string) $cookie->getValue();
      $body['token'] = $cookieValue;
      $request = $request->withParsedBody($body);
    }


    $response = $next($request, $response);
    $response->getBody()->rewind();
    $result = json_decode($response->getBody()->getContents(), true);


    $static_routes = [
      '/login' => true,
      '/register' => true,
      '/asset/submit' => true,
    ];
    $queryUri = false;

    $route = $request->getAttribute('route');
    $path = $request->getUri()->getPath();

    if(isset($static_routes['/' . $path])) {
      $queryUri = '/' . $path;
    } else if($route) {
      $queryUri = $route->getPattern();
    } else

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
        'GET /asset/submit' => 'submit_asset',
        'GET /asset/{id}' => 'asset',
        'GET /asset/{id}/edit' => 'edit_asset',
        'GET /asset/edit/{id}' => 'asset_edit',
        //'/register' => 'registered',
        'GET /login' => 'login',
        'GET /register' => 'register',
        'ERROR' => 'error',
      ];

      if(isset($result['error']) && !isset($template_names[$queryUri])) {
        $queryUri = 'ERROR';
      }

      if(isset($template_names[$queryUri])) {
        $response = new \Slim\Http\Response();
        $errorResponse = new \Slim\Http\Response();
        $params = [
          'data' => $result,
          'basepath' => dirname($request->getUri()->getBasePath()) . '/frontend',
          'bowerpath' => dirname($request->getUri()->getBasePath()) . '/bower_components',
          'path' => $path,
          'params' => $request->getQueryParams(),
          'categories' => [], // Filled later
          'constants' => $this->constants,
          //'body' => $request->getParsedBody(),
        ];

        if(isset($body['token'])) {
          $token = $this->tokens->validate($body['token']);
          $error = $this->utils->get_user_from_token_data(false, $errorResponse, $token, $user);
          if(!$error) {
            $params['user'] = $user;
          }
        }

        $query_categories = $this->queries['category']['list'];
        $query_categories->bindValue(':category_type', '%');
        $query_categories->execute();

        $error = $this->utils->error_reponse_if_query_bad(false, $errorResponse, $query_categories);
        $error = $this->utils->error_reponse_if_query_no_results($error, $errorResponse, $query_categories);
        if(!$error) {
          $params['categories'] = $query_categories->fetchAll();
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
