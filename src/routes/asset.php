<?php
// Asset routes

// Searches through the list of assets
$app->get('/asset', function ($request, $response, $args) {
  $params = $request->getQueryParams();
  $query = $this->queries['asset']['search'];
  $query_count = $this->queries['asset']['search_count'];

  $category = '%';
  $filter = '%';
  $order_column = 'rating';
  $order_direction = 'desc';
  $page_size = 10;
  $max_page_size = 500;
  $page_offset = 0;
  if(isset($params['category'])) {
    $category = $params['category'];
  }
  if(isset($params['filter'])) {
    $filter = '%'.preg_replace('/[[:punct:]]+/', '%', $params['filter']).'%';
  }
  if(isset($params['max_results'])) {
    $page_size = min(abs((int) $params['max_results']), $max_page_size);
  }
  if(isset($params['page'])) {
    $page_offset = abs((int) $params['page']) * $page_size;
  } elseif(isset($params['offset'])) {
    $page_offset = abs((int) $params['offset']);
  }
  if(isset($params['sort'])) {
    $column_mapping = [
      'rating' => 'rating',
      'cost' => 'cost',
      'name' => 'title',
      // TODO: downloads, updated
    ];
    if(isset($column_mapping[$params['sort']])) {
      $order_column = $column_mapping[$params['sort']];
    }
  }
  if(isset($params['reverse'])) {
    $order_direction = 'asc';
  }

  $query->bindValue(':category', $category, PDO::PARAM_INT);
  $query->bindValue(':filter', $filter);
  $query->bindValue(':order', $order_column);
  $query->bindValue(':order_direction', $order_direction);
  $query->bindValue(':page_size', $page_size, PDO::PARAM_INT);
  $query->bindValue(':skip_count', $page_offset, PDO::PARAM_INT);
  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  $query_count->bindValue(':category', $category, PDO::PARAM_INT);
  $query_count->bindValue(':filter', $filter, PDO::PARAM_INT);
  $query_count->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_count);
  if($error) return $response;

  $total_count = $query_count->fetchAll()[0]['count'];

  return $response->withJson([
    'result' => $query->fetchAll(),
    'page' => floor($page_offset / $page_size),
    'pages' => ceil($total_count / $page_size),
    'page_length' => $page_size,
    'total_items' => (int) $total_count,
  ], 200);
});

// Get information for a single asset
$app->get('/asset/{id}', function ($request, $response, $args) {
  $query = $this->queries['asset']['get_one'];

  $query->bindValue(':id', (int) $args['id'], PDO::PARAM_INT);
  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  if($query->rowCount() <= 0) {
    return $response->withJson([
      'error' => 'Couldn\'t find asset with id '.$args['id'].'!'
    ], 404);
  }

  $output = $query->fetchAll();
  $asset_info = [];
  $previews = [];

  foreach ($output as $row) {
    foreach ($row as $column => $value) {
      if($value!==null) {
        if($column==='preview_id') {
          $previews[] = [];
        } elseif($column==="type" || $column==="link" || $column==="thumbnail") {
            $previews[count($previews) - 1][$column] = $value;
        } elseif($column==="accepted") {
          $asset_info["accepted"] = ($value != "UNACCEPTED");
        } else {
          $asset_info[$column] = $value;
        }
      }
    }
  }
  foreach ($previews as $i => $_) {
    if(!isset($previews[$i]['thumbnail']) || $previews[$i]['thumbnail'] == '') {
      if($previews[$i]['type'] == 'video') {
        $matches = [];
        if(preg_match('|youtube.com/watch\\?v=([^&]+)|', $previews[$i]['link'], $matches)) {
          $previews[$i]['thumbnail'] = 'http://img.youtube.com/vi/'.$matches[1].'/default.jpg';
        }
      } else {
        $previews[$i]['thumbnail'] = $previews[$i]['link'];
      }
    }
  }

  $asset_info['previews'] = $previews;

  return $response->withJson($asset_info, 200);
});

// Submit an asset
$app->post('/asset', function ($request, $response, $args) {
  $body = $request->getParsedBody();
  $query = $this->queries['asset']['submit'];

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

  $query->bindValue(':user_id', $user_id, PDO::PARAM_INT);
  $query->bindValue(':title', $body["title"]);
  $query->bindValue(':description', $body["description"]);
  $query->bindValue(':category_id', $body["category_id"]);
  $query->bindValue(':cost', $body["cost"]);
  $query->bindValue(':version_string', $body["version_string"]);
  $query->bindValue(':download_url', $body["download_url"]);
  $query->bindValue(':browse_url', $body["browse_url"]);
  $query->bindValue(':icon_url', $body["icon_url"]);
  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  $id = $this->db->lastInsertId();


  return $response->withJson([
    'id' => $id,
    'url' => $_SERVER['HTTP_HOST'] . dirname($request->getUri()->getBasePath()) . '/api/asset/' . $id,
  ], 200);
});

// Edit an asset
$app->post('/asset/{id}', function ($request, $response, $args) {
  $body = $request->getParsedBody();
  $query_permissions = $this->queries['asset']['get_one_bare'];

  $error = $this->utils->error_reponse_if_missing_or_not_string(false, $response, $body, 'token');
  if($error) return $response;

  $token_data = $this->tokens->validate($body['token']);
  $user_id = $this->utils->get_user_id_from_token_data($token_data);
  if($user_id === false) {
    return $response->withJson([
      'error' => 'Invalid token supplied',
    ], 400);
  }

  $query_permissions->bindValue(':asset_id', (int) $args['id'], PDO::PARAM_INT);
  $query_permissions->execute();
  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_permissions);
  if($error) return $response;

  $asset = $query_permissions->fetchAll()[0];
  if((int) $asset['user_id'] !== (int) $user_id) {
    // NOTE: If this fails somehow, it is possible that the user will be able to modify all assets.
    return $response->withJson([
      'error' => 'You are not authorized to update this asset',
    ], 403);
  }

  $updated = false;
  $updated_version = false;

  if(isset($body['title'])) {
    $query = $this->queries['asset']['update_details'];

    $error = $this->utils->error_reponse_if_missing_or_not_string(false, $response, $body, 'title');
    $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'description');
    $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'category_id');
    $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'cost');
    $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'icon_url');
    if($error) return $response;

    $query->bindValue(':asset_id', (int) $args['id'], PDO::PARAM_INT);

    $query->bindValue(':title', $body['title']);
    $query->bindValue(':description', $body['description']);
    $query->bindValue(':category_id', (int) $body['category_id'], PDO::PARAM_INT);
    $query->bindValue(':cost', $body['cost']);
    $query->bindValue(':icon_url', $body['icon_url']);

    $query->execute();
    $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
    if($error) return $response;
    if($query->rowCount() > 0) {
      $updated = true;
    }
  }

  if(isset($body['download_url'])) {
    $query = $this->queries['asset']['update_version'];

    $error = $this->utils->error_reponse_if_missing_or_not_string(false, $response, $body, 'version_string');
    $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'download_url');
    $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'browse_url');
    if($error) return $response;

    $query->bindValue(':asset_id', (int) $args['id'], PDO::PARAM_INT);
    $query->bindValue(':version_string', $body['version_string']);
    $query->bindValue(':download_url', $body['download_url']);
    $query->bindValue(':browse_url', $body['browse_url']);

    $query->execute();
    $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
    if($error) return $response;
    if($query->rowCount() > 0) {
      $updated = true;
      $updated_version = true;
    }
  }

  return $response->withJson([
    'id' => $args['id'],
    'updated' => $updated,
    'version_changed' => $updated_version
  ], 200);
});
