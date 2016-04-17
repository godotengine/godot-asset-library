<?php
// Routes


// Initializes the connection by sending all categories available
$app->get('/configure', function ($request, $response, $args) {
  $query = $this->queries['category']['list'];
  $query->execute();
  return $response->withJson([
    'categories' => $query->fetchAll(),
  ], 200);
});

// Searches through the list of assets
$app->get('/search', function ($request, $response, $args) {
  $params = $request->getQueryParams();
  $query = $this->queries['asset']['search'];

  $category = '%';
  $filter = '%';
  $order_column = 'title';
  $page_size = 10;
  $max_page_size = 10;
  $page_offset = 0;
  if(isset($params['category'])) {
    $category = $params['category'];
  }
  if(isset($params['filter'])) {
    $filter = "%{$params['filter']}%";
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

  $query->bindValue(':category', $category, PDO::PARAM_INT);
  $query->bindValue(':filter', $filter);
  $query->bindValue(':order', $order_column);
  $query->bindValue(':page_size', $page_size, PDO::PARAM_INT);
  $query->bindValue(':skip_count', $page_offset, PDO::PARAM_INT);
  $query->execute();

  return $response->withJson([
    'result' => $query->fetchAll(),
  ], 200);
});

// Get information for a single asset
$app->get('/info', function ($request, $response, $args) {
  $params = $request->getQueryParams();
  $query = $this->queries['asset']['get_one'];

  if(!isset($params['id'])) {
    return $response->withJson([
      'error' => 'No id parameter present on request!'
    ], 400);
  }

  $query->bindValue(':id', (int) $params['id'], PDO::PARAM_INT);
  $query->execute();

  if($query->rowCount() <= 0) {
    return $response->withJson([
      'error' => 'Couldn\'t find any asset with the given id!'
    ], 400);
  }

  $output = $query->fetchAll();
  $asset_info = [];
  $previews = [];

  foreach ($output as $row) {
    foreach ($row as $column => $value) {
      if($value!==null) {
        if($column==='id') {
          $previews[] = [];
        } elseif($column==="type" || $column==="link") {
            $previews[count($previews) - 1][$column] = $value;
        } else {
          $asset_info[$column] = $value;
        }
      }
    }
  }

  $asset_info['previews'] = $previews;

  return $response->withJson($asset_info, 200);
});
