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
        'addon' => '0',
        'project' => '1',
        'any' => '%',
    ]),
    'support_level' => double_map([
        'testing' => 0,
        'community' => 1,
        'official' => 2,
    ]),
    'user_type' => double_map([
        'normal' => 0,
        'verified' => 5,
        'editor' => 25,
        'moderator' => 50,
        'admin' => 100,
    ]),
    'download_provider' => double_map([
        'Custom' => -1,
        'GitHub' => 0,
        'GitLab' => 1,
        'BitBucket' => 2,
        'Gogs/Gitea' => 3,
        'cgit' => 4,
    ]),
    'asset_edit_fields' => [
        'title', 'description', 'category_id', 'godot_version',
        'version_string', 'cost',
        'download_provider', 'download_commit', 'browse_url', 'issues_url', 'icon_url',
    ],
    'asset_edit_preview_fields' => [
        'type', 'link', 'thumbnail',
    ],
    'special_godot_versions' => double_map([
        0 => 'unknown',
        9999999 => 'custom_build'
    ]),
    'common_godot_versions' => [
        // '1.0',
        // '1.1',
        '2.0',
        '2.1',
        '2.2',
        '3.0',
        '3.1',
        '3.2',
        'unknown',
        'custom_build',
    ]
];
