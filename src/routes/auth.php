<?php
// Auth routes


// Initializes the connection by sending all categories available
$app->get('/configure', function ($request, $response, $args) {
  $query = $this->queries['category']['list'];
  $query->execute();

  $errorResponse = $this->utils->error_reponse_if_query_bad($response, $query);
  if($errorResponse) return $errorResponse;

  if(isset($request->getQueryParams()['session'])) {
    $id = openssl_random_pseudo_bytes(16);
    $token = $this->tokens->generate([
      'session' => base64_encode($id),
    ]);

    return $response->withJson([
      'categories' => $query->fetchAll(),
      'token' => $token,
      'login_url' => $_SERVER['HTTP_HOST'] . dirname($request->getUri()->getBasePath()) . '/html#/login/' . urlencode($token),
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

  $errorResponse = $this->utils->error_reponse_if_missing_or_not_string($response, $body, 'username');
  $errorResponse = $this->utils->error_reponse_if_missing_or_not_string($errorResponse, $body, 'email');
  $errorResponse = $this->utils->error_reponse_if_missing_or_not_string($errorResponse, $body, 'password');
  if($errorResponse) return $errorResponse;

  $query_check->bindValue(':username', $body['username']);
  $query_check->execute();

  $errorResponse = $this->utils->error_reponse_if_query_bad($errorResponse, $query_check);
  if($errorResponse) return $errorResponse;

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

  $errorResponse = $this->utils->error_reponse_if_query_bad($errorResponse, $query);
  if($errorResponse) return $errorResponse;

  return $response->withJson([
    'username' => $body['username'],
    'registered' => true
  ], 200);
});

$app->post('/login', function ($request, $response, $args) {
  $body = $request->getParsedBody();
  $query = $this->queries['user']['get_by_username'];

  $errorResponse = $this->utils->error_reponse_if_missing_or_not_string($response, $body, 'username');
  $errorResponse = $this->utils->error_reponse_if_missing_or_not_string($errorResponse, $body, 'password');
  if($errorResponse) return $errorResponse;

  $query->bindValue(':username', $body['username']);
  $query->execute();

  $errorResponse = $this->utils->error_reponse_if_query_bad($errorResponse, $query);
  if($errorResponse) return $errorResponse;

  $user = $query->fetchAll()[0];

  if(isset($body['token'])) {
    $token_data = $this->tokens->validate($body['token']);

    if(!$token_data || !isset($token_data["session"])) {
      return $response->withJson([
        'error' => 'Invalid token supplied'
      ], 400);
    }

    $sesion_query = $queries['user']['set_session_token'];
    $sesion_query->bindValue(':id', (int) $user['id'], PDO::PARAM_INT);
    $sesion_query->bindValue(':session_token', $body['token']);
    $sesion_query->execute();
    $errorResponse = $this->utils->error_reponse_if_query_bad($errorResponse, $sesion_query);
    if($errorResponse) return $errorResponse;
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
