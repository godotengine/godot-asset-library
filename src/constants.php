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
        'featured' => 2,
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
        'Gogs/Gitea/Codeberg' => 3,
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
        '2.0',
        '2.1',
        '2.2',
        '3.0',
        '3.1',
        '3.2',
        '3.3',
        '3.4',
        '3.5',
        '3.6',
        '4.0',
        '4.1',
        '4.2',
        '4.3',
        '4.4',
        'unknown',
        'custom_build',
    ],
    'licenses' => [
        'MIT' => 'MIT',
        'MPL-2.0' => 'MPL-2.0',
        'GPLv3' => 'GPL v3',
        'GPLv2' => 'GPL v2',
        'LGPLv3' => 'LGPL v3',
        'LGPLv2.1' => 'LGPL v2.1',
        'LGPLv2' => 'LGPL v2',
        'AGPLv3' => 'AGPL v3',
        'EUPL-1.2' => 'European Union Public License 1.2',
        'Apache-2.0' => 'Apache 2.0',
        'CC0' => 'CC0 1.0 Universal',
        'CC-BY-4.0' => 'CC BY 4.0 International',
        'CC-BY-3.0' => 'CC BY 3.0 Unported',
        'CC-BY-SA-4.0' => 'CC BY-SA 4.0 International',
        'CC-BY-SA-3.0' => 'CC BY-SA 3.0 Unported',
        'BSD-0-Clause' => 'BSD 0-clause License',
        'BSD-1-Clause' => 'BSD 1-clause License',
        'BSD-2-Clause' => 'BSD 2-clause License',
        'BSD-3-Clause' => 'BSD 3-clause License',
        'BSL-1.0' => 'Boost Software License',
        'ISC' => 'ISC License',
        'Unlicense' => 'The Unlicense License',
        'Proprietary' => 'Proprietary (see LICENSE file)',
    ]
];
