<?php

if(isset($frontend) && $frontend) {
  $app->add(function ($request, $response, $next) {
    $response = $next($request, $response);
    $response->getBody()->rewind();
    $result = json_decode($response->getBody()->getContents(), true);

    $route = $request->getAttribute('route');
    if(!$route || !$result) {
      return $response;
    }

    if($request->getMethod() == 'GET') {

      $parts = explode('/', $route->getPattern());
      $template_name = '';
      $pluralize = true;
      foreach ($parts as $i => $part) {
        if($part != '') {
          if($part[0] == '{') {
            $pluralize = false;
          } else {
            if($i != 1) {
              $template_name .= '_';
            }
            $template_name .= $part;
          }
        }
      }
      if($pluralize) {
        $template_name .= 's';
      }

      $response = new \Slim\Http\Response();

      return $this->renderer->render($response, $template_name . '.phtml', $result);
    } else if(isset($result['url'])) {
      $response = new \Slim\Http\Response(303);
      return $response->withHeader('Location', $result['url']);
    }
  });
}