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
      'updated' => 'modify_date'
      // TODO: downloads
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
