<?php

function double_map($array)
{
  foreach ($array as $key => $value) {
    $array[(string) $value] = $key;
    $array[$value] = $key;
  }
  return $array;
}

return $constants = [
  'edit_status' => double_map([
    'new' => 0,
    'in_review' => 1,
    'accepted' => 2,
    'rejected' => 3,
  ]),
  'user_type' => double_map([
    'normal' => 0,
    'moderator' => 1,
  ]),
  'asset_edit_fields' => [
    'title', 'description', 'category_id',
    'version_string', 'cost',
    'download_url', 'browse_url', 'icon_url',
  ]
];