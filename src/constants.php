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
  'edit_preview_operation' => double_map([
    'insert' => 0,
    'remove' => 1,
    'update' => 2,
  ]),
  'category_type' => double_map([
    'addon' => 0,
    'project' => 1,
    'any' => '%',
  ]),
  'support_level' => double_map([
    'testing' => 0,
    'community' => 1,
    'official' => 2,
  ]),
  'user_type' => double_map([
    'normal' => 0,
    'editor' => 25,
    'moderator' => 50,
    'admin' => 100,
  ]),
  'download_provider' => double_map([
    'GitHub' => 0,
    'GitLab' => 1,
    'BitBucket' => 2,
    'Gogs' => 3,
    'cgit' => 4,
  ]),
  'asset_edit_fields' => [
    'title', 'description', 'category_id',
    'version_string', 'cost',
    'download_provider', 'download_commit', 'browse_url', 'issues_url', 'icon_url',
  ],
  'asset_edit_preview_fields' => [
    'type', 'link', 'thumbnail',
  ]
];