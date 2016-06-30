<?php
// Auth routes


// Initializes the connection by sending all categories available
$app->get('/configure', function ($request, $response, $args) {
  $params = $request->getQueryParams();

  $category_type = $this->constants['category_type']['addon'];

  if(isset($params['type']) && isset($this->constants['category_type'][$params['type']])) {
    $category_type = $this->constants['category_type'][$params['type']];
  }

  $query = $this->queries['category']['list'];
  $query->bindValue(':category_type', $category_type);
  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  if(isset($request->getQueryParams()['session'])) {
    $id = openssl_random_pseudo_bytes(16);
    $token = $this->tokens->generate([
      'session' => base64_encode($id),
    ]);

    return $response->withJson([
      'categories' => $query->fetchAll(),
      'token' => $token,
      'login_url' => $_SERVER['HTTP_HOST'] . dirname($request->getUri()->getBasePath()) . 'frontend/login#' . urlencode($token),
      // ^ TODO: Make those routes actually work
    ], 200);

  } else {
    return $response->withJson([
      'categories' => $query->fetchAll(),
    ], 200);
  }
});

$app->post('/register', function ($request, $response, $args) {
  $body = $request->getParsedBody();
  $query = $this->queries['user']['register'];
  $query_check = $this->queries['user']['get_by_username'];

  $error = $this->utils->error_reponse_if_missing_or_not_string(false, $response, $body, 'username');
  $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'email');
  $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'password');
  if($error) return $response;

  $query_check->bindValue(':username', $body['username']);
  $query_check->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_check);
  if($error) return $response;

  if($query_check->rowCount() > 0) {
    return $response->withJson([
      'error' => 'Username already taken!',
    ], 409);
  }

  $password_hash = password_hash($body['password'], PASSWORD_BCRYPT, $this->get('settings')['auth']['bcryptOptions']);

  $query->bindValue(':username', $body['username']);
  $query->bindValue(':email', $body['email']); // TODO: Verify email.
  $query->bindValue(':password_hash', $password_hash);

  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  return $response->withJson([
    'username' => $body['username'],
    'registered' => true,
    'url' => 'login',
  ], 200);
});

$app->post('/login', function ($request, $response, $args) {
  $body = $request->getParsedBody();
  $query = $this->queries['user']['get_by_username'];

  $error = $this->utils->error_reponse_if_missing_or_not_string(false, $response, $body, 'username');
  $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'password');
  if($error) return $response;

  $query->bindValue(':username', $body['username']);
  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  $error = $this->utils->error_reponse_if_query_no_results(false, $response, $query);
  if($error) return $response;

  $user = $query->fetchAll()[0];

  if(isset($body['token'])) {
    $token_data = $this->tokens->validate($body['token']);

    if(!$token_data || !isset($token_data->session)) {
      return $response->withJson([
        'error' => 'Invalid token supplied'
      ], 400);
    }

    $query_session = $this->queries['user']['set_session_token'];
    $query_session->bindValue(':id', (int) $user['id'], PDO::PARAM_INT);
    $query_session->bindValue(':session_token', $token_data->session);
    $query_session->execute();
    $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_session);
    if($error) return $response;
  }

  $token = $this->tokens->generate([
    'user_id' => $user['id']
  ]);

  if(password_verify($body['password'], $user['password_hash'])) {
    return $response->withJson([
      'username' => $body['username'],
      'token' => $token,
      'authenticated' => true,
    ], 200);
  }
});

if(isset($frontend) && $frontend) { // Doesn't work for non-frontend, since we can't unissue tokens -- to logout from api/ just drop the token.
  $app->get('/logout', function ($request, $response, $args) {
    return $response->withJson([
      'authenticated' => false,
      'token' => '',
      'url' => 'login',
    ], 200);
  });
}
