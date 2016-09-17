<?php
// Asset editing routes

function _submit_asset_edit($c, $response, $body, $user_id, $asset_id=-1) {
  $query = $c->queries['asset_edit']['submit'];
  $query->bindValue(':user_id', $user_id, PDO::PARAM_INT);
  $query->bindValue(':asset_id', $asset_id, PDO::PARAM_INT);
  if($asset_id == -1) {
    $error = _insert_asset_edit_fields($c, false, $response, $query, $body, true);
    if($error) return $response;
  } else {
    $query_asset = $c->queries['asset']['get_one_bare'];
    $query_asset->bindValue(':asset_id', (int) $asset_id, PDO::PARAM_INT);
    $query_asset->execute();

    $error = $c->utils->error_reponse_if_query_bad(false, $response, $query_asset);
    if($error) return $response;

    $asset = $query_asset->fetchAll()[0];

    $error = _insert_asset_edit_fields($c, false, $response, $query, $body, false, $asset);
    if($error) return $response;
  }

  $query->execute();
  $error = $c->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  $id = $c->db->lastInsertId();

  if(isset($body['previews'])) {
    $error = _add_previews_to_edit($c, $error, $response, $id, $body['previews'], null, $asset_id==-1);
    if($error) return $response;
  }

  return $response->withJson([
    'id' => $id,
    'url' => 'asset/edit/' . $id,
  ], 200);
}

function _insert_asset_edit_fields($c, $error, &$response, $query, $body, $required=false, $bare_asset=null) {
  if($error) return true;

  foreach ($c->constants['asset_edit_fields'] as $i => $field) {
    if(isset($body[$field]) && $field == 'download_provider') {
      if(isset($c->constants['download_provider'][$body[$field]])) {
        $body[$field] = (string) ((int) $c->constants['download_provider'][$body[$field]]);
      } else {
        $body[$field] = 0;
      }
    }
    if(!$required) {
      if(isset($body[$field]) && ($bare_asset === null || $bare_asset[$field] != $body[$field])) {
        $query->bindValue(':' . $field, $body[$field]);
      } else {
        $query->bindValue(':' . $field, null, PDO::PARAM_NULL);
      }
    } else {
      $error = $c->utils->error_reponse_if_missing_or_not_string($error, $response, $body, $field);
      if(!$error) $query->bindValue(':' . $field, $body[$field]);
    }

    if($error) {
      return $error;
    }
  }
  return $error;
}

function _add_previews_to_edit($c, $error, &$response, $edit_id, $previews, $asset=null, $required=false) {
  if($error) return true;

  foreach ($previews as $i => $preview) {
    if(!isset($preview['enabled']) || !$preview['enabled']) continue;
    if($required || !isset($preview['edit_preview_id'])) {

      $query = $c->queries['asset_edit']['add_preview'];

      $error = $c->utils->error_reponse_if_missing_or_not_string($error, $response, $preview, 'operation');
      if($error) return $error;

      $operation = $c->constants['edit_preview_operation']['insert'];

      if(!$required && $asset !== null && isset($c->constants['edit_preview_operation'][$preview['operation']])) {
        $operation = $c->constants['edit_preview_operation'][$preview['operation']];
      }
      $query->bindValue(':operation',(int) $operation, PDO::PARAM_INT);

      if($operation == $c->constants['edit_preview_operation']['insert']) {
        $query->bindValue(':preview_id', -1, PDO::PARAM_INT);
      } else {
        $error = $c->utils->error_reponse_if_missing_or_not_string($error, $response, $preview, 'preview_id');
        if($error) return $error;

        if($asset !== null) {
          $query_check = $c->queries['asset']['get_one_preview_bare'];
          $query_check->bindValue(':preview_id', (int) $preview['preview_id'], PDO::PARAM_INT);
          $query_check->execute();
          $error = $c->utils->error_reponse_if_query_bad(false, $response, $query_check);
          $error = $c->utils->error_reponse_if_query_no_results($error, $response, $query_check);
          if($error) return $error;

          $original_preview = $query_check->fetchAll()[0];
          if($original_preview['asset_id'] != $asset['asset_id']) {
            $response = $response->withJson(['error' => 'Invalid preview id.'], 400);
            return true;
          }
        } else {
          $response = $response->withJson(['error' => 'Bug in processing code, please report.'], 500);
          return true;
        }

        $query->bindValue(':preview_id', (int) $preview['preview_id'], PDO::PARAM_INT);
      }

    } elseif(isset($preview['remove']) && $preview['remove']) {

      $query = $c->queries['asset_edit']['remove_preview'];
      $query->bindValue(':edit_preview_id', (int) $preview['edit_preview_id'], PDO::PARAM_INT);
      $query->bindValue(':edit_id', (int) $edit_id, PDO::PARAM_INT);
      $query->execute();
      $error = $c->utils->error_reponse_if_query_bad(false, $response, $query);
      if($error) return $error;

      continue;
    } else {
      $query = $c->queries['asset_edit']['update_preview'];
      $error = $c->utils->error_reponse_if_missing_or_not_string($error, $response, $preview, 'edit_preview_id');
      if($error) return $error;
      $query->bindValue(':edit_preview_id', (int) $preview['edit_preview_id'], PDO::PARAM_INT);
    }
    $query->bindValue(':edit_id', (int) $edit_id, PDO::PARAM_INT);

    foreach ($c->constants['asset_edit_preview_fields'] as $i => $field) {
      if(!$required) {
        if(isset($preview[$field]) && !(isset($original_preview) && $original_preview[$field] == $preview[$field])) {
          $query->bindValue(':' . $field, $preview[$field]);
        } elseif(!isset($preview[$field]) && !isset($original_preview)) {
          $query->bindValue(':' . $field, $preview[$field]);
        } else {
          $query->bindValue(':' . $field, null, PDO::PARAM_NULL);
        }
      } else {
        $error = $c->utils->error_reponse_if_missing_or_not_string($error, $response, $preview, $field);
        if(!$error) $query->bindValue(':' . $field, $preview[$field]);
      }
    }
    if($error) return $error;

    $query->execute();
    $error = $c->utils->error_reponse_if_query_bad(false, $response, $query);
    if($error) return $error;
  }

  return $error;
}

$app->get('/asset/edit', function ($request, $response, $args) {

  // Enable if needed (for now, transparent to all) [Also change request to post]
  // $error = $this->utils->ensure_logged_in(false, $response, $body, $user);
  // $error = $this->utils->error_reponse_if_not_user_has_level($error, $response, $user, 'moderator');
  // if($error) return $response;

  $params = $request->getQueryParams();

  $asset_id = '%';
  $filter = '%';
  $statuses = [];
  $page_size = 10;
  $max_page_size = 500;
  $page_offset = 0;
  if(isset($params['asset'])) {
    $asset_id = (int) $params['asset'];
  }
  if(isset($params['status'])) { // Expects the param like `new+in_review`
    if(is_array($params['status'])) {
      foreach($params['status'] as $key => $value) {
        if($value && isset($this->constants['edit_status'][$key])) {
          array_push($statuses, (int) $this->constants['edit_status'][$key]);
        }
      }
    } else {
      foreach(explode(' ', $params['status']) as $key => $value) { // `+` is changed to ` ` automatically
        if(isset($this->constants['edit_status'][$value])) {
          array_push($statuses, (int) $this->constants['edit_status'][$value]);
        }
      }
    }
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

  if(count($statuses) === 0) {
    $statuses = [0, 1]; // New + In Review
  }
  $statuses = implode('|', $statuses);

  $query = $this->queries['asset_edit']['search'];
  $query->bindValue(':filter', $filter);
  $query->bindValue(':asset_id', $asset_id);
  $query->bindValue(':statuses_regex', $statuses);
  $query->bindValue(':page_size', $page_size, PDO::PARAM_INT);
  $query->bindValue(':skip_count', $page_offset, PDO::PARAM_INT);
  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  $query_count = $this->queries['asset_edit']['search_count'];
  $query_count->bindValue(':filter', $filter);
  $query_count->bindValue(':asset_id', $asset_id);
  $query_count->bindValue(':statuses_regex', $statuses);
  $query_count->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_count);
  if($error) return $response;

  $total_count = $query_count->fetchAll()[0]['count'];

  $asset_edits = $query->fetchAll();

  $context = $this;
  $asset_edits = array_map(function($asset_edit) use($context) {
    $asset_edit['status'] = $context->constants['edit_status'][(int) $asset_edit['status']];
    $asset_edit['support_level'] = $context->constants['support_level'][(int) $asset_edit['support_level']];
    return $asset_edit;
  }, $asset_edits);

  return $response->withJson([
    'result' => $asset_edits,
    'page' => floor($page_offset / $page_size),
    'pages' => ceil($total_count / $page_size),
    'page_length' => $page_size,
    'total_items' => (int) $total_count,
  ], 200);
});

// Get an edit
$get_edit = function ($request, $response, $args) {
  $query = $this->queries['asset_edit']['get_one'];
  $query->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  $error = $this->utils->error_reponse_if_query_no_results($error, $response, $query);
  if($error) return $response;

  $output = $query->fetchAll();

  $previews = [];
  $previews_last_i = null;
  $unedited_previews = [];
  $unedited_previews_last_i = null;
  $asset_edit = [];

  foreach ($output as $row) {

    foreach ($row as $column => $value) {
      if($previews_last_i !== null && ($column==='preview_id' || $column==='type' || $column==='link' || $column==='thumbnail')) {
        $previews[$previews_last_i][$column] = $value;
      } elseif($previews_last_i !== null && $column==='operation') {
        $previews[$previews_last_i][$column] = $this->constants['edit_preview_operation'][(int) $value];
      } elseif($unedited_previews_last_i !== null && ($column==='unedited_type' || $column==='unedited_link' || $column==='unedited_thumbnail')) {
        $unedited_previews[$unedited_previews_last_i][substr($column, strlen('unedited_'))] = $value;
      }  elseif($column==='orig_type' || $column==='orig_link' || $column==='orig_thumbnail') {
        if($value != null && $previews_last_i !== null) {
          $previews[$previews_last_i]['original'][substr($column, strlen('orig_'))] = $value;
        }
      } elseif($value!==null) {
        if($column==='edit_preview_id') {
          $previews[$value] = ['edit_preview_id' => $value];
          $previews_last_i = $value;
        } elseif($column==='unedited_preview_id') {
          $unedited_previews[$value] = ['preview_id' => $value];
          $unedited_previews_last_i = $value;
        } elseif($column==='status') {
          $asset_edit['status'] = $this->constants['edit_status'][(int) $value];
        } elseif($column==='download_provider') {
          $asset_edit['download_provider'] = $this->constants['download_provider'][(int) $value];
        } else {
          $asset_edit[$column] = $value;
        }
      } elseif($column!=='edit_preview_id' && $column!=='preview_id') {
        $asset_edit[$column] = $value;
      }
    }
  }

  if($asset_edit['asset_id'] != -1) {
    foreach($previews as $preview) {
      if(isset($preview['preview_id']) && isset($unedited_previews[$preview['preview_id']])) {
        unset($unedited_previews[$preview['preview_id']]);
      }
    }
    $asset_edit['previews'] = array_merge(array_values($previews), array_values($unedited_previews));
  } else {
    $asset_edit['previews'] = array_values($previews);
  }

  if($asset_edit['asset_id'] != -1) {
    $query_asset = $this->queries['asset']['get_one_bare'];
    $query_asset->bindValue(':asset_id', (int) $asset_edit['asset_id'], PDO::PARAM_INT);
    $query_asset->execute();

    $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_asset);
    $error = $this->utils->error_reponse_if_query_no_results($error, $response, $query_asset);
    if($error) return $response;

    $asset = $query_asset->fetchAll()[0];

    $asset_edit['original'] = $asset;
    $asset_edit['original']['download_provider'] = $this->constants['download_provider'][$asset['download_provider']];

    if($asset_edit['browse_url'] || $asset_edit['download_provider'] || $asset_edit['download_commit']) {
      $asset_edit['download_url'] = $this->utils->get_computed_download_url(
        $asset_edit['browse_url'] ?: $asset_edit['original']['browse_url'],
        $asset_edit['download_provider'] ?: $asset_edit['original']['download_provider'],
        $asset_edit['download_commit'] ?: $asset_edit['original']['download_commit'],
        $warning
      );
    } else {
      $asset_edit['download_url'] = null;
    }

    $asset_edit['original']['download_url'] = $this->utils->get_computed_download_url($asset_edit['original']['browse_url'], $asset_edit['original']['download_provider'], $asset_edit['original']['download_commit'], $warning);
  } else {
    $asset_edit['download_url'] = $this->utils->get_computed_download_url($asset_edit['browse_url'], $asset_edit['download_provider'], $asset_edit['download_commit'], $warning);
  }

  if($warning != null) {
    $asset_edit['warning'] = $warning;
  }
  if($asset_edit['download_commit'] == 'master') {
    if(isset($asset_edit['warning'])) {
      $asset_edit['warning'] .= "\n\n";
    } else {
      $asset_edit['warning'] = '';
    }
    $asset_edit['warning'] .= "Giving 'master' (or any other branch name) as the commit to be downloaded is not recommended, since it would invalidate the asset when you push a new version (since we ensure the version is kept the same via a sha256 hash of the zip). You can try using tags instead.";
  }


  return $response->withJson($asset_edit, 200);
};

// Binding to multiple routes
$app->get('/asset/edit/{id:[0-9]+}', $get_edit);
if(isset($frontend) && $frontend) {
  $app->get('/asset/edit/{id:[0-9]+}/edit', $get_edit);
}

// Submit an asset
$app->post('/asset', function ($request, $response, $args) {
  $body = $request->getParsedBody();

  $error = $this->utils->ensure_logged_in(false, $response, $body, $user);
  if($error) return $response;

  return _submit_asset_edit($this, $response, $body, $user['user_id'], -1);
});


// Edit an existing asset
$app->post('/asset/{id:[0-9]+}', function ($request, $response, $args) {
  $body = $request->getParsedBody();

  $error = $this->utils->ensure_logged_in(false, $response, $body, $user);
  if($error) return $response;

  // Ensure the author is editing the asset
  $query_asset = $this->queries['asset']['get_one_bare'];
  $query_asset->bindValue(':asset_id', (int) $args['id'], PDO::PARAM_INT);
  $query_asset->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_asset);
  if($error) return $response;

  $asset = $query_asset->fetchAll()[0];

  if((int) $asset['user_id'] !== (int) $user['user_id']) {
    $error = $this->utils->error_reponse_if_not_user_has_level($error, $response, $user, 'editor', 'You are not authorized to update this asset');
    if($error) return $response;
  }

  return _submit_asset_edit($this, $response, $body, $user['user_id'], (int) $args['id']);
});


// Edit an existing edit
$app->post('/asset/edit/{id:[0-9]+}', function ($request, $response, $args) {
  $body = $request->getParsedBody();

  $error = $this->utils->ensure_logged_in(false, $response, $body, $user);
  if($error) return $response;

  // Fetch the edit to check the user id
  $query_edit = $this->queries['asset_edit']['get_one_bare'];
  $query_edit->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
  $query_edit->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_edit);
  $error = $this->utils->error_reponse_if_query_no_results($error, $response, $query_edit);
  if($error) return $response;

  $asset_edit = $query_edit->fetchAll()[0];

  if((int) $asset_edit['user_id'] !== (int) $user['user_id']) {
    return $response->withJson([
      'error' => 'You are not authorized to update this asset edit',
    ], 403);
  }

  if((int) $asset_edit['status'] !== $this->constants['edit_status']['new']) {
    return $response->withJson([
      'error' => 'You are no longer allowed to update this asset edit, please make a new one',
    ], 403);
  }

  // Build query
  $query = $this->queries['asset_edit']['update'];
  $query->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);


  $asset = null;
  if($asset_edit['asset_id'] != -1) {
    $query_asset = $this->queries['asset']['get_one_bare'];
    $query_asset->bindValue(':asset_id', (int) $asset_edit['asset_id'], PDO::PARAM_INT);
    $query_asset->execute();

    $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_asset);
    if($error) return $response;

    $asset = $query_asset->fetchAll()[0];
  }

  $error = _insert_asset_edit_fields($this, false, $response, $query, $body, false, $asset);
  if($error) return $response;

  $query->execute();
  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  if(isset($body['previews'])) {
    $error = _add_previews_to_edit($this, $error, $response, $args['id'], $body['previews'], $asset, false);
    if($error) return $response;
  }

  return $response->withJson([
    'id' => $args['id'],
    'url' => 'asset/edit/' . $args['id'],
  ], 200);
});


// Accept an edit
$app->post('/asset/edit/{id:[0-9]+}/accept', function ($request, $response, $args) {
  $body = $request->getParsedBody();

  $error = $this->utils->ensure_logged_in(false, $response, $body, $user);
  $error = $this->utils->error_reponse_if_not_user_has_level($error, $response, $user, 'moderator', 'You are not authorized to accept this asset edit');
  if($error) return $response;

  // Get the edit
  $query_edit = $this->queries['asset_edit']['get_one'];
  $query_edit->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
  $query_edit->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_edit);
  $error = $this->utils->error_reponse_if_query_no_results($error, $response, $query_edit);
  if($error) return $response;

  $asset_edit_previews = $query_edit->fetchAll();
  $asset_edit = $asset_edit_previews[0];
  if((int) $asset_edit['status'] !== $this->constants['edit_status']['in_review']) {
    return $response->withJson([
      'error' => 'The edit should be in review in order to be accepted',
    ], 403);
  }

  // Start building the query
  $query = null;

  if((int) $asset_edit['asset_id'] === -1) {
    $query = $this->queries['asset']['apply_creational_edit'];
    $query->bindValue(':user_id', (int) $asset_edit['user_id'], PDO::PARAM_INT);
  } else {
    $query = $this->queries['asset']['apply_edit'];
    $query->bindValue(':asset_id', (int) $asset_edit['asset_id'], PDO::PARAM_INT);
  }

  // Params
  $update_version = false;
  foreach ($this->constants['asset_edit_fields'] as $i => $field) {
    if(isset($asset_edit[$field]) && $asset_edit[$field] !== null) {
      $query->bindValue(':' . $field, $asset_edit[$field]);
      $update_version = $update_version || ($field === 'browse_url' || $field === 'download_provider' || $field === 'download_commit' || $field === 'version_string');
    } else {
      $query->bindValue(':' . $field, null, PDO::PARAM_NULL);
    }
  }

  if($update_version) {
    $error = $this->utils->error_reponse_if_missing_or_not_string(false, $response, $body, 'hash');
    if($error) return $response;

    $body['hash'] = trim($body['hash']);
    if(sizeof(preg_grep('/^[a-f0-9]{64}$/', [$body['hash']])) == 0) {
      return $response->withJson([
        'error' => 'Invalid hash given. Expected 64 lowercase hexadecimal digits.',
      ]);
    }

    $query->bindValue(':update_version', 1, PDO::PARAM_INT);
    $query->bindValue(':download_hash', $body['hash']);
  } else {
    if(isset($body['hash']) && trim($body['hash']) != '') {
      $body['hash'] = trim($body['hash']);
      if(sizeof(preg_grep('/^[a-f0-9]{64}$/', [$body['hash']])) == 0) {
        return $response->withJson([
          'error' => 'Invalid hash given. Expected either nothing or 64 lowercase hexadecimal digits.',
        ]);
      }
      $query->bindValue(':update_version', 1, PDO::PARAM_INT);
      $query->bindValue(':download_hash', $body['hash']);
    } else {
      $query->bindValue(':update_version', 0, PDO::PARAM_INT);
      $query->bindValue(':download_hash', null, PDO::PARAM_NULL);
    }
  }

  // Update the status to prevent race conditions
  $query_status = $this->queries['asset_edit']['set_status_and_reason'];

  $query_status->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
  $query_status->bindValue(':status', (int) $this->constants['edit_status']['accepted'], PDO::PARAM_INT);
  $query_status->bindValue(':reason', '');

  $query_status->execute();
  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_status);
  $error = $this->utils->error_reponse_if_query_no_results(false, $response, $query_status); // Important: Ensure that something was actually changed
  if($error) return $response;

  // Run
  $query->execute();
  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  // Update the id in case it was newly-created
  if((int) $asset_edit['asset_id'] === -1) {
    $asset_edit['asset_id'] = $this->db->lastInsertId();

    $query_update_id = $this->queries['asset_edit']['set_asset_id'];

    $query_update_id->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
    $query_update_id->bindValue(':asset_id', (int) $asset_edit['asset_id'], PDO::PARAM_INT);

    $query_update_id->execute();

    $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_update_id);
    if($error) return $response;
    $query_update_id->closeCursor();
  }

  $previews_processed = [];
  foreach($asset_edit_previews as $i => $preview) {
    if(!isset($preview['edit_preview_id']) || $preview['edit_preview_id'] == null || isset($previews_processed[$preview['edit_preview_id']])) {
      continue;
    }
    $previews_processed[$preview['edit_preview_id']] = true;
    $operation = $this->constants['edit_preview_operation'][$preview['operation']];
    $query_apply_preview = $this->queries['asset']['apply_preview_edit_' . $operation];

    $query_apply_preview->bindValue(':asset_id', (int) $asset_edit['asset_id']);

    if($operation == 'remove' || $operation == 'update') {
      $query_apply_preview->bindValue(':preview_id', (int) $preview['preview_id']);
    }

    if($operation == 'insert' || $operation == 'update') {
      foreach ($this->constants['asset_edit_preview_fields'] as $i => $field) {
        if(isset($preview[$field])) {
          $query_apply_preview->bindValue(':' . $field, $preview[$field]);
        } else {
          $query_apply_preview->bindValue(':' . $field, null, PDO::PARAM_NULL);
        }
      }
    }

    $query_apply_preview->execute();
    $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_apply_preview);
    if($error) return $response;
  }

  return $response->withJson([
    'id' => $asset_edit['asset_id'],
    'url' => 'asset/' . $asset_edit['asset_id'],
  ], 200);
});

// Review an edit
$app->post('/asset/edit/{id:[0-9]+}/review', function ($request, $response, $args) {
  $body = $request->getParsedBody();

  $error = $this->utils->ensure_logged_in(false, $response, $body, $user);
  $error = $this->utils->error_reponse_if_not_user_has_level($error, $response, $user, 'moderator', 'You are not authorized to put in review this asset edit');
  if($error) return $response;

  // Get the edit
  $query_edit = $this->queries['asset_edit']['get_one_bare'];
  $query_edit->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
  $query_edit->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query_edit);
  $error = $this->utils->error_reponse_if_query_no_results($error, $response, $query_edit);
  if($error) return $response;

  $asset_edit = $query_edit->fetchAll()[0];
  if((int) $asset_edit['status'] > $this->constants['edit_status']['in_review']) {
    return $response->withJson([
      'error' => 'The edit should be new in order to be put in review',
    ], 403);
  }

  // Do the change
  $query = $this->queries['asset_edit']['set_status_and_reason'];

  $query->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
  $query->bindValue(':status', (int) $this->constants['edit_status']['in_review'], PDO::PARAM_INT);
  $query->bindValue(':reason', '');

  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  $asset_edit['status'] = 'in_review'; // Prepare to send
  $asset_edit['url'] = 'asset/edit/' . $args['id'];

  return $response->withJson($asset_edit, 200);
});


// Reject an edit
$app->post('/asset/edit/{id:[0-9]+}/reject', function ($request, $response, $args) {
  $body = $request->getParsedBody();

  $error = $this->utils->ensure_logged_in(false, $response, $body, $user);
  $error = $this->utils->error_reponse_if_not_user_has_level($error, $response, $user, 'moderator', 'You are not authorized to reject this asset edit');
  $error = $this->utils->error_reponse_if_missing_or_not_string($error, $response, $body, 'reason');
  if($error) return $response;

  $query = $this->queries['asset_edit']['set_status_and_reason'];

  $query->bindValue(':edit_id', (int) $args['id'], PDO::PARAM_INT);
  $query->bindValue(':status', (int) $this->constants['edit_status']['rejected'], PDO::PARAM_INT);
  $query->bindValue(':reason', $body['reason']);

  $query->execute();

  $error = $this->utils->error_reponse_if_query_bad(false, $response, $query);
  if($error) return $response;

  return $response->withJson([
    'rejected' => true,
    'url' => 'asset/edit/' . $args['id'],
  ], 200);
});
