<?php
// Asset editing routes

// Submit an asset
$app->post('/asset', function ($request, $response, $args) {
  $body = $request->getParsedBody();

  $error = $this->utils->error_reponse_if_missing_or_not_string(false, $response, $body, 'token');
  $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'title');
  $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'description');
  $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'category_id');
  $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'cost');
  $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'version_string');
  $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'download_url');
  $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'browse_url');
  $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'icon_url');
  if($error) return $response;

  $token_data = $this->tokens->validate($body['token']);
  $user_id = $this->utils->get_user_id_from_token_data($token_data);
  if($user_id === false) {
    return $response->withJson([
      'error' => 'Invalid token supplied'
    ], 400);
  }

  $query = $this->queries['asset_edit']['submit'];
  $query->bindValue(':user_id', $user_id, PDO::PARAM_INT);
  $query->bindValue(':asset_id', -1, PDO::PARAM_INT);

  foreach ($this->constants['asset_edit_fields'] as $i => $field) {
    if(isset($body[$field])) {
      $query->bindValue(':' . $field, $body[$field]);
    } else {
      $query->bindValue(':' . $field, null, PDO::PARAM_NULL);
    }
  }

  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  $id = $this->db->lastInsertId();

  return $response->withJson([
    'id' => $id,
    'url' => $_SERVER['HTTP_HOST'] . dirname($request->getUri()->getBasePath()) . '/api/asset/edit/' . $id,
  ], 200);
});

// Edit an existing asset
$app->post('/asset/{id}', function ($request, $response, $args) {
  $body = $request->getParsedBody();

  $error = $this->utils->error_reponse_if_missing_or_not_string(false, $response, $body, 'token');
  if($error) return $response;

  $token_data = $this->tokens->validate($body['token']);
  $user_id = $this->utils->get_user_id_from_token_data($token_data);
  if($user_id === false) {
    return $response->withJson([
      'error' => 'Invalid token supplied',
    ], 400);
  }

  $query_permissions = $this->queries['asset']['get_one_bare'];
  $query_permissions->bindValue(':asset_id', (int) $args['id'], PDO::PARAM_INT);
  $query_permissions->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_permissions);
  if($error) return $response;

  $asset = $query_permissions->fetchAll()[0];
  if((int) $asset['user_id'] !== (int) $user_id) {
    return $response->withJson([
      'error' => 'You are not authorized to update this asset',
    ], 403);
  }


  $query = $this->queries['asset_edit']['submit'];
  $query->bindValue(':user_id', $user_id, PDO::PARAM_INT);
  $query->bindValue(':asset_id', (int) $args['id'], PDO::PARAM_INT);

  foreach ($this->constants['asset_edit_fields'] as $i => $field) {
    if(isset($body[$field])) {
      $query->bindValue(':' . $field, $body[$field]);
    } else {
      $query->bindValue(':' . $field, null, PDO::PARAM_NULL);
    }
  }

  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  $id = $this->db->lastInsertId();

  return $response->withJson([
    'id' => $id,
    'url' => $_SERVER['HTTP_HOST'] . dirname($request->getUri()->getBasePath()) . '/api/asset/edit/' . $id,
  ], 200);
});

// Edit an existing edit
$app->post('/asset/edit/{id}', function ($request, $response, $args) {
  $body = $request->getParsedBody();

  $error = $this->utils->error_reponse_if_missing_or_not_string(false, $response, $body, 'token');
  if($error) return $response;

  $token_data = $this->tokens->validate($body['token']);
  $user_id = $this->utils->get_user_id_from_token_data($token_data);
  if($user_id === false) {
    return $response->withJson([
      'error' => 'Invalid token supplied',
    ], 400);
  }

  $query_permissions = $this->queries['asset_edit']['get_one'];
  $query_permissions->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
  $query_permissions->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_permissions);
  $error = $this->utils->error_reponse_if_query_no_results($error, $response, $query_permissions);
  if($error) return $response;

  $asset_edit = $query_permissions->fetchAll()[0];
  if((int) $asset_edit['user_id'] !== (int) $user_id) {
    return $response->withJson([
      'error' => 'You are not authorized to update this asset edit',
    ], 403);
  }
  if((int) $asset_edit['status'] !== $this->constants['edit_status']['new']) {
    return $response->withJson([
      'error' => 'You are not longer allowed to update this asset edit, please make a new one',
    ], 403);
  }

  $query = $this->queries['asset_edit']['update'];
  $query->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);

  foreach ($this->constants['asset_edit_fields'] as $i => $field) {
    if(isset($body[$field])) {
      $query->bindValue(':' . $field, $body[$field]);
    } else {
      $query->bindValue(':' . $field, null, PDO::PARAM_NULL);
    }
  }

  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  return $response->withJson([
    'id' => $args['id'],
    'url' => $_SERVER['HTTP_HOST'] . dirname($request->getUri()->getBasePath()) . '/api/asset/edit/' . $args['id'],
  ], 200);
});

// Check an existing edit
$app->get('/asset/edit/{id}', function ($request, $response, $args) {
  $body = $request->getParsedBody();

  $query = $this->queries['asset_edit']['get_one'];
  $query->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  $error = $this->utils->error_reponse_if_query_no_results($error, $response, $query);
  if($error) return $response;

  $asset_edit = $query->fetchAll()[0];

  unset($asset_edit['user_id']);
  if(isset($this->constants['edit_status'][$asset_edit['status']])) {
    $asset_edit['status'] = $this->constants['edit_status'][$asset_edit['status']];
  } else {
    $asset_edit['status'] = 'unknown - ' . $asset_edit['status'];
  }

  return $response->withJson($asset_edit, 200);
});

// Accept an edit
$app->post('/asset/edit/{id}/accept', function ($request, $response, $args) {
  $body = $request->getParsedBody();

  $error = $this->utils->error_reponse_if_missing_or_not_string(false, $response, $body, 'token');
  if($error) return $response;

  $token_data = $this->tokens->validate($body['token']);
  $user_id = $this->utils->get_user_id_from_token_data($token_data);
  if($user_id === false) {
    return $response->withJson([
      'error' => 'Invalid token supplied',
    ], 400);
  }

  $query_permissions = $this->queries['user']['get_one'];
  $query_permissions->bindValue(':id', (int) $user_id, PDO::PARAM_INT);
  $query_permissions->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_permissions);
  $error = $this->utils->error_reponse_if_query_no_results($error, $response, $query_permissions);
  if($error) return $response;

  $user = $query_permissions->fetchAll()[0];
  if((int) $user['type'] !== $this->constants['user_type']['moderator']) {
    return $response->withJson([
      'error' => 'You are not authorized to accept this asset edit',
    ], 403);
  }

  $query_edit = $this->queries['asset_edit']['get_one'];
  $query_edit->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
  $query_edit->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_edit);
  $error = $this->utils->error_reponse_if_query_no_results($error, $response, $query_edit);
  if($error) return $response;

  $asset_edit = $query_edit->fetchAll()[0];
  if((int) $asset_edit['status'] !== $this->constants['edit_status']['in_review']) {
    return $response->withJson([
      'error' => 'The edit should be in review in order to be accepted',
    ], 403);
  }

  if($asset_edit['asset_id'] === -1) {
    $query = $this->queries['asset']['apply_creational_edit'];
    $query->bindValue(':user_id', (int) $asset_edit['user_id'], PDO::PARAM_INT);
  } else {
    $query = $this->queries['asset']['apply_edit'];
    $query->bindValue(':asset_id', (int) $asset_edit['asset_id'], PDO::PARAM_INT);
  }

  $update_version = false;

  foreach ($this->constants['asset_edit_fields'] as $i => $field) {
    if(isset($asset_edit[$field]) && $asset_edit[$field] !== null) {
      $query->bindValue(':' . $field, $asset_edit[$field]);
      if($field === 'download_url' || $field === 'browse_url' || $field === 'version_string') {
        $update_version = true;
      }
    } else {
      $query->bindValue(':' . $field, null, PDO::PARAM_NULL);
    }
  }
  $query->bindValue(':update_version', (int) $update_version, PDO::PARAM_INT);

  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  if($asset_edit['asset_id'] === -1) {
    $asset_edit['asset_id'] = $this->db->lastInsertId();

    $query_update_version = $this->queries['asset_edit']['set_asset_id'];

    $query_update_version->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
    $query_update_version->bindValue(':asset_id', (int) $asset_edit['asset_id'], PDO::PARAM_INT);

    $query_update_version->execute();

    $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_update_version);
    if($error) return $response;
  }

  $query_update_status = $this->queries['asset_edit']['set_status_and_reason'];

  $query_update_status->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
  $query_update_status->bindValue(':status', (int) $this->constants['edit_status']['accepted'], PDO::PARAM_INT);
  $query_update_status->bindValue(':reason', "");

  $query_update_status->execute();

  return $response->withJson([
    'id' => $asset_edit['asset_id'],
    'url' => $_SERVER['HTTP_HOST'] . dirname($request->getUri()->getBasePath()) . '/api/asset/' . $asset_edit['asset_id'],
  ], 200);
});

// Review an edit
$app->post('/asset/edit/{id}/review', function ($request, $response, $args) {
  $body = $request->getParsedBody();

  $error = $this->utils->error_reponse_if_missing_or_not_string(false, $response, $body, 'token');
  if($error) return $response;

  $token_data = $this->tokens->validate($body['token']);
  $user_id = $this->utils->get_user_id_from_token_data($token_data);
  if($user_id === false) {
    return $response->withJson([
      'error' => 'Invalid token supplied',
    ], 400);
  }

  $query_permissions = $this->queries['user']['get_one'];
  $query_permissions->bindValue(':id', (int) $user_id, PDO::PARAM_INT);
  $query_permissions->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_permissions);
  $error = $this->utils->error_reponse_if_query_no_results($error, $response, $query_permissions);
  if($error) return $response;

  $user = $query_permissions->fetchAll()[0];
  if((int) $user['type'] !== $this->constants['user_type']['moderator']) {
    return $response->withJson([
      'error' => 'You are not authorized to accept this asset edit',
    ], 403);
  }

  $query_update_status = $this->queries['asset_edit']['set_status_and_reason'];

  $query_update_status->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
  $query_update_status->bindValue(':status', (int) $this->constants['edit_status']['in_review'], PDO::PARAM_INT);
  $query_update_status->bindValue(':reason', "");

  $query_update_status->execute();

  return $response->withJson([
    'in_review' => true,
  ], 200);
});

// Reject an edit
$app->post('/asset/edit/{id}/reject', function ($request, $response, $args) {
  $body = $request->getParsedBody();

  $error = $this->utils->error_reponse_if_missing_or_not_string(false, $response, $body, 'token');
  $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'reason');
  if($error) return $response;

  $token_data = $this->tokens->validate($body['token']);
  $user_id = $this->utils->get_user_id_from_token_data($token_data);
  if($user_id === false) {
    return $response->withJson([
      'error' => 'Invalid token supplied',
    ], 400);
  }

  $query_permissions = $this->queries['user']['get_one'];
  $query_permissions->bindValue(':id', (int) $user_id, PDO::PARAM_INT);
  $query_permissions->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_permissions);
  $error = $this->utils->error_reponse_if_query_no_results($error, $response, $query_permissions);
  if($error) return $response;

  $user = $query_permissions->fetchAll()[0];
  if((int) $user['type'] !== $this->constants['user_type']['moderator']) {
    return $response->withJson([
      'error' => 'You are not authorized to accept this asset edit',
    ], 403);
  }

  $query = $this->queries['asset_edit']['set_status_and_reason'];

  $query->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
  $query->bindValue(':status', (int) $this->constants['edit_status']['rejected'], PDO::PARAM_INT);
  $query->bindValue(':reason', $body['reason']);

  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  return $response->withJson([
    'rejected' => true,
  ], 200);
});