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
  'category_type' => double_map([
    'addon' => 0,
    'project' => 1,
    'any' => '%'
  ]),
  'support_level' => double_map([
    'testing' => 0,
    'community' => 1,
    'official' => 2
  ]),
  'user_type' => double_map([
    'normal' => 0,
    'moderator' => 50,
    'admin' => 100,
  ]),
  'asset_edit_fields' => [
    'title', 'description', 'category_id',
    'version_string', 'cost',
    'download_url', 'browse_url', 'icon_url',
  ]
];