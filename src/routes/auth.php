<?php
// Auth routes


// Initializes the connection by sending all categories available
$app->get('/configure', function ($request, $response, $args) {
  $query = $this->queries['category']['list'];
  $query->execute();

  if($query->errorCode() != '00000') {
    $this->logger->error($query->errorCode(), $query->errorInfo());
    return $response->withJson([
      'error' => 'An error occured while fetching results from DB!'
    ], 500);
  }

  if(isset($request->getQueryParams()['session'])) {
    $token = $this->tokens->generate([
      'session' => openssl_random_pseudo_bytes(16)
    ]);

    return $response->withJson([
      'categories' => $query->fetchAll(),
      'token' => $token,
      'login_url' => $_SERVER['HTTP_HOST'] . dirname($request->getUri()->getBasePath()) . '/html#/login/' . $token,
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
  if(!isset($body['username']) || !is_string($body['username']) || strlen($body['username']) < 5) {
    return $response->withJson([
      'error' => 'Username is required, provided is not a string, or it is too short!'
    ], 400);
  }
  if(!isset($body['email']) || !is_string($body['email'])) {
    return $response->withJson([
      'error' => 'Email is required, or provided is not a string!'
    ], 400);
  }
  if(!isset($body['password']) || !is_string($body['password'])) {
    return $response->withJson([
      'error' => 'Password is required, or provided is not a string!'
    ], 400);
  }

  $query_check->bindValue(':username', $body['username']);
  $query_check->execute();

  if($query_check->errorCode() != '00000') {
    $this->logger->error($query->errorCode(), $query->errorInfo());
    return $response->withJson([
      'error' => 'An error occured while checking for existing user in DB!'
    ], 500);
  }

  if($query_check->rowCount() > 0) {
    return $response->withJson([
      'error' => 'Username already taken!',
    ], 409);
  }

  $query->bindValue(':username', $body['username']);
  $query->bindValue(':email', $body['email']); // TODO: Verify email.
  $password_hash = password_hash($body['password'], PASSWORD_BCRYPT, $this->get('settings')['auth']['bcryptOptions']);
  $query->bindValue(':password_hash', $password_hash);

  $query->execute();

  if($query->errorCode() != '00000') {
    $this->logger->error($query->errorCode(), $query->errorInfo());
    return $response->withJson([
      'error' => 'An error occured while adding user to DB!'
    ], 500);
  }

  return $response->withJson([
    'username' => $body['username'],
    'registered' => true
  ], 200);
});

$app->post('/login', function ($request, $response, $args) {
  $body = $request->getParsedBody();
  $query = $this->queries['user']['get_by_username'];

  if(!isset($body['username']) || !is_string($body['username'])) {
    return $response->withJson([
      'error' => 'Username is required, or provided is not a string!'
    ], 400);
  }
  if(!isset($body['password']) || !is_string($body['password'])) {
    return $response->withJson([
      'error' => 'Password is required, or provided is not a string!'
    ], 400);
  }

  if(isset($body['token'])) {
    $token_data = $this->tokens->validate($body['token']);
    if(!$token_data) {
      return $response->withJson([
        'error' => 'Invalid token supplied'
      ], 400);
    }
    // TODO: Authenticate user session.
  }

  $query->bindValue(':username', $body['username']);
  $query->execute();

  if($query->errorCode() != '00000') {
    $this->logger->error($query->errorCode(), $query->errorInfo());
    return $response->withJson([
      'error' => 'An error occured while fetching user from DB!'
    ], 500);
  }

  $user = $query->fetchAll()[0];

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
