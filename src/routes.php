<?php
// Routes


//get all categories
$app->get('/configure', function ($request, $response, $args) {
  global $db;
  $output = array();
  $result = $db->query("SELECT * FROM as_categories ORDER BY id");
  while ($row = $result->fetch_assoc()) {
    $output[] = $row;
  }
  echo '{
   	"categories":';
  echo json_encode($output);
  echo '}';
  return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
});

// search
$app->get('/search', function ($request, $response, $args) {
  global $db;
  $sort = $db->real_escape_string($_GET['sort']);
  $orderby = "";
  if(!isset($_GET['filter'])) {
    $filter = "";
  } else {
    $filter = $db->real_escape_string($_GET['filter']);
  }
  if(!isset($_GET['category'])) {
    $category = "%";
  } else {
    $category = $db->real_escape_string($_GET['category']);
  }
  if($sort == "rating") {
    $orderby = "rating";
  }
  else if ($sort == "downloads") {
    $oderby = "title";
  }
  else if ($sort == "name") {
    $oderby = "title";
  }
  else if ($sort == "cost") {
    $oderby = "cost";
  }
  else if ($sort == "updated") {
    $oderby = "title";
  } else {
    $orderby = "title";
  }
  $output = array();
  $result = $db->query("SELECT * FROM as_assets WHERE category_id LIKE '$category' AND (title LIKE '%$filter%' OR cost LIKE '%$filter%' OR author LIKE '%$filter%') ORDER BY '$orderby'");
  while ($row = $result->fetch_assoc()) {
    $output[] = $row;
  }
  echo '{
    "result":';
  echo json_encode($output);
  echo '}';
  return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
});

// get assets
$app->get('/asset', function ($request, $response, $args) {
  global $db;
  $id = $db->real_escape_string($_GET['id']);
  $result = $db->query("SELECT * FROM as_assets WHERE asset_id=$id");
  $row = $result->fetch_assoc();
  $info = $row;
  $result = $db->query("SELECT * FROM as_asset_previews WHERE asset_id=$id");
  $previews = array();
  while ($row = $result->fetch_assoc()) {
    unset($row['id']);
    $previews[] = $row;
  }
  echo '{
    "info":';
  echo json_encode($info);
  echo ',"previews":';
  echo json_encode($previews);
  echo '}';
  return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
});
