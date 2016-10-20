<?php

if(FRONTEND) {
  $container = $app->getContainer();

  $app->get('/', function ($request, $response) {
    return $response->withJson(['url' => 'asset']);
  });

  $app->add(function ($request, $response, $next) {
    $cookie = $this->cookies['requestCookies']->get($request, 'token');
    $body = $request->getParsedBody();
    if($cookie->getValue() !== null && !isset($body['token'])) {
      $cookieValue = (string) $cookie->getValue();
      $body['token'] = $cookieValue;
      $request = $request->withParsedBody($body);
    }
    $response->getBody()->rewind();
    $preresult = json_decode($response->getBody()->getContents(), true);
    if(!isset($preresult['error'])) {
      $response = $next($request, $response);
    }

    $static_routes = [
      '/login' => true,
      '/register' => true,
      '/forgot_password' => true,
      '/change_password' => true,
      '/asset/submit' => true,
    ];
    $queryUri = false;

    $route = $request->getAttribute('route');
    $path = $request->getUri()->getPath();

    if(substr($path, 0, 8) == 'frontend') {
      $response = $response->withHeader('Location', $request->getUri()->getBasePath() . substr($path, 8) . '?' . $request->getUri()->getQuery());
    }

    if(isset($static_routes['/' . $path])) {
      $queryUri = '/' . $path;
    } elseif($route) {
      $queryUri = $route->getPattern();
    } else {
      return $response;
    }

    $queryUri = $request->getMethod() . ' ' . $queryUri;

    if($route) {
      $response->getBody()->rewind();
      $result = json_decode($response->getBody()->getContents(), true);
      if($result === null) {
        return $response;
        //$result = ['error' => 'Can\'t decode api response - ' . $response->getBody()->getContents()];
      }
    } else {
      $result = [];
    }


    if(isset($result['url'])) {
      $response = new \Slim\Http\Response(303);
      $response = $response->withHeader('Location', $request->getUri()->getBasePath() . '/' . $result['url']);
    } else {
      if(isset($result['token'])) {
        $body['token'] = $result['token'];
      }
      $template_names = [
        'GET /user/feed' => 'feed',

        'GET /asset' => 'assets',
        'GET /asset/submit' => 'submit_asset',
        'GET /asset/{id:[0-9]+}' => 'asset',
        'GET /asset/{id:[0-9]+}/edit' => 'edit_asset',

        'GET /asset/edit' => 'asset_edits',
        'GET /asset/edit/{id:[0-9]+}' => 'asset_edit',
        'GET /asset/edit/{id:[0-9]+}/edit' => 'edit_asset_edit',

        'GET /login' => 'login',
        'ERROR POST /login' => 'login',
        'GET /register' => 'register',
        'ERROR POST /register' => 'register',
        'GET /forgot_password' => 'forgot_password',
        'POST /forgot_password' => 'forgot_password_result',
        'GET /reset_password' => 'reset_password',
        'GET /change_password' => 'change_password',
        'ERROR POST /change_password' => 'change_password',

        'ERROR' => 'error',
      ];

      if(isset($result['error'])) {
        if(isset($template_names['ERROR ' . $queryUri])) {
          $queryUri = 'ERROR ' . $queryUri;
        } else {
          $queryUri = 'ERROR';
        }
      }

      if(isset($template_names[$queryUri])) {
        $response = new \Slim\Http\Response();
        $errorResponse = new \Slim\Http\Response();
        $params = [
          'data' => $result,
          'basepath' => $request->getUri()->getBasePath(). '',
          'bowerpath' => $request->getUri()->getBasePath() . '/bower_components',
          'path' => $path,
          'params' => $request->getQueryParams(),
          'query' => $request->getUri()->getQuery(),
          'categories' => [], // Filled later
          'constants' => $this->constants,
          'csrf_name_key' => $this->csrf->getTokenNameKey(),
          'csrf_name' => $request->getAttribute('csrf_name'),
          'csrf_value_key' => $this->csrf->getTokenValueKey(),
          'csrf_value' => $request->getAttribute('csrf_value'),
          //'body' => $request->getParsedBody(),
        ];

        if(isset($body['token'])) {
          $token = $this->tokens->validate($body['token']);
          $error = $this->utils->get_user_from_token_data(false, $errorResponse, $token, $user);
          if(!$error) {
            $params['user'] = $user;
          } else {
            $error = $this->utils->get_user_from_token_data(false, $errorResponse, $token, $reset_user, true);
            if(!$error) {
              $params['reset_user'] = $reset_user;
            }
          }
        }

        $query_categories = $this->queries['category']['list'];
        $query_categories->bindValue(':category_type', '%');
        $query_categories->execute();

        $error = $this->utils->error_reponse_if_query_bad(false, $errorResponse, $query_categories);
        $error = $this->utils->error_reponse_if_query_no_results($error, $errorResponse, $query_categories);
        if(!$error) {
          $categories = $query_categories->fetchAll();
          foreach ($categories as $key => $value) {
            $params['categories'][$value['id']] = $value;
          }
        }

        $response = $this->renderer->render($response, $template_names[$queryUri] . '.phtml', $params);
      }
    }

    if(isset($result['token'])) {
      $response = $this->cookies['responseCookies']->set($response, $this->cookies['setCookie']('token')
        ->withValue($result['token'])
        ->withDomain($_SERVER['HTTP_HOST'])
        ->withPath($request->getUri()->getBasePath())
        ->withHttpOnly(true)
      );
    }
    return $response;
  });

  // Adding after the real middleware, since it has to run first... o.O
  $app->add($container->get('csrf'));

  $container->get('csrf')->setFailureCallable(function ($request, $response, $next) {
    $response = $response->withJson([
      'error' => 'CSRF check failed',
    ]);
    return $next($request, $response);
  });
}
