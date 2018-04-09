<?php

namespace Godot\AssetLibrary\Controllers;

class AssetEditController
{
    private $container;

    public function __construct($container) // TODO: Passing the container directly to serve as a service locator is discouraged
    {
        $this->container = $container;
    }

    public function list($request, $response)
    {
        $container = $this->container;

        $params = $request->getQueryParams();

        $asset_id = '%';
        $filter = '%';
        $username = '%';
        $statuses = [];
        $page_size = 10;
        $max_page_size = 500;
        $page_offset = 0;
        if (isset($params['asset'])) {
            $asset_id = (int) $params['asset'];
        }
        if (isset($params['status'])) { // Expects the param like `new+in_review`
            if (is_array($params['status'])) {
                foreach ($params['status'] as $key => $value) {
                    if ($value && isset($container->constants['edit_status'][$key])) {
                        array_push($statuses, (int) $container->constants['edit_status'][$key]);
                    }
                }
            } else {
                foreach (explode(' ', $params['status']) as $key => $value) { // `+` is changed to ` ` automatically
                    if (isset($container->constants['edit_status'][$value])) {
                        array_push($statuses, (int) $container->constants['edit_status'][$value]);
                    }
                }
            }
        }
        if (isset($params['filter'])) {
            $filter = '%'.preg_replace('/[[:punct:]]+/', '%', $params['filter']).'%';
        }
        if (isset($params['user'])) {
            $username = $params['user'];
        }
        if (isset($params['max_results'])) {
            $page_size = min(abs((int) $params['max_results']), $max_page_size);
        }
        if (isset($params['page'])) {
            $page_offset = abs((int) $params['page']) * $page_size;
        } elseif (isset($params['offset'])) {
            $page_offset = abs((int) $params['offset']);
        }

        if (count($statuses) === 0) {
            $statuses = [0, 1]; // New + In Review
        }
        $statuses = implode('|', $statuses);

        $query = $container->queries['asset_edit']['search'];
        $query->bindValue(':filter', $filter);
        $query->bindValue(':username', $username);
        $query->bindValue(':asset_id', $asset_id);
        $query->bindValue(':statuses_regex', $statuses);
        $query->bindValue(':page_size', $page_size, \PDO::PARAM_INT);
        $query->bindValue(':skip_count', $page_offset, \PDO::PARAM_INT);
        $query->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            return $response;
        }

        $query_count = $container->queries['asset_edit']['search_count'];
        $query_count->bindValue(':filter', $filter);
        $query_count->bindValue(':username', $username);
        $query_count->bindValue(':asset_id', $asset_id);
        $query_count->bindValue(':statuses_regex', $statuses);
        $query_count->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_count);
        if ($error) {
            return $response;
        }

        $total_count = $query_count->fetchAll()[0]['count'];

        $asset_edits = $query->fetchAll();


        $asset_edits = array_map(function ($asset_edit) use ($container) {
            $asset_edit['status'] = $container->constants['edit_status'][(int) $asset_edit['status']];
            $asset_edit['godot_version'] = $container->utils->getFormattedGodotVersion((int) $asset_edit['godot_version']);
            $asset_edit['support_level'] = $container->constants['support_level'][(int) $asset_edit['support_level']];
            return $asset_edit;
        }, $asset_edits);

        return $response->withJson([
            'result' => $asset_edits,
            'page' => floor($page_offset / $page_size),
            'pages' => ceil($total_count / $page_size),
            'page_length' => $page_size,
            'total_items' => (int) $total_count,
        ], 200);
    }

    public function getOne($request, $response, $id)
    {
        $container = $this->container;

        $query = $container->queries['asset_edit']['get_one'];
        $query->bindValue(':edit_id', (int) $id, \PDO::PARAM_INT);
        $query->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        $error = $container->utils->errorResponseIfQueryNoResults($error, $response, $query);
        if ($error) {
            return $response;
        }

        $warning = null;

        $output = $query->fetchAll();

        $previews = [];
        $previews_last_i = null;
        $unedited_previews = [];
        $unedited_previews_last_i = null;
        $asset_edit = [];
        $has_changes = false;

        foreach ($output as $row) {
            foreach ($row as $column => $value) {
                if ($value !== null && $column !== 'edit_preview_id' && $column !== 'edit_id' && $column !== 'asset_id' && $column !== 'user_id' && $column !== 'status' && $column !== 'reason' && $column !== 'author') {
                    $has_changes = true;
                }

                if ($previews_last_i !== null && ($column === 'preview_id' || $column === 'type' || $column === 'link' || $column === 'thumbnail')) {
                    $previews[$previews_last_i][$column] = $value;
                } elseif ($previews_last_i !== null && $column === 'operation') {
                    $previews[$previews_last_i][$column] = $container->constants['edit_preview_operation'][(int) $value];
                } elseif ($unedited_previews_last_i !== null && ($column === 'unedited_type' || $column === 'unedited_link' || $column === 'unedited_thumbnail')) {
                    $unedited_previews[$unedited_previews_last_i][substr($column, strlen('unedited_'))] = $value;
                } elseif ($column === 'orig_type' || $column === 'orig_link' || $column === 'orig_thumbnail') {
                    if ($value !== null && $previews_last_i !== null) {
                        $previews[$previews_last_i]['original'][substr($column, strlen('orig_'))] = $value;
                    }
                } elseif ($value !== null) {
                    if ($column === 'edit_preview_id') {
                        $previews[$value] = ['edit_preview_id' => $value];
                        $previews_last_i = $value;
                    } elseif ($column === 'unedited_preview_id') {
                        $unedited_previews[$value] = ['preview_id' => $value];
                        $unedited_previews_last_i = $value;
                    } elseif ($column === 'status') {
                        $asset_edit['status'] = $container->constants['edit_status'][(int) $value];
                    } elseif ($column === 'download_provider') {
                        $asset_edit['download_provider'] = $container->constants['download_provider'][(int) $value];
                    } else {
                        $asset_edit[$column] = $value;
                    }
                } elseif ($column !== 'edit_preview_id' && $column !== 'preview_id') {
                    $asset_edit[$column] = $value;
                }
            }
        }

        $asset_edit['asset_id'] = (int) $asset_edit['asset_id'];

        if (!$has_changes) {
            $warning .= "No changes are present.";
        }

        if ($asset_edit['asset_id'] !== -1) {
            foreach ($previews as $preview) {
                if (isset($preview['preview_id']) && isset($unedited_previews[$preview['preview_id']])) {
                    unset($unedited_previews[$preview['preview_id']]);
                }
            }
            $asset_edit['previews'] = array_merge(array_values($previews), array_values($unedited_previews));
        } else {
            $asset_edit['previews'] = array_values($previews);
        }

        if ($asset_edit['asset_id'] !== -1) {
            $query_asset = $container->queries['asset']['get_one_bare'];
            $query_asset->bindValue(':asset_id', (int) $asset_edit['asset_id'], \PDO::PARAM_INT);
            $query_asset->execute();

            $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_asset);
            $error = $container->utils->errorResponseIfQueryNoResults($error, $response, $query_asset);
            if ($error) {
                return $response;
            }

            $asset = $query_asset->fetchAll()[0];

            $asset_edit['original'] = $asset;
            $asset_edit['original']['download_provider'] = $container->constants['download_provider'][$asset['download_provider']];
            $asset_edit['original']['godot_version'] = $container->utils->getFormattedGodotVersion($asset['godot_version']);

            $repository_changed = $asset_edit['browse_url'] !== null || $asset_edit['download_provider'] !== null;

            if ($repository_changed || $asset_edit['download_commit'] !== null) {
                $asset_edit['download_url'] = $container->utils->getComputedDownloadUrl(
                    $asset_edit['browse_url'] ?: $asset_edit['original']['browse_url'],
                    $asset_edit['download_provider'] ?: $asset_edit['original']['download_provider'],
                    $asset_edit['download_commit'] ?: $asset_edit['original']['download_commit'],
                    $warning
                );
            } else {
                $asset_edit['download_url'] = null;
            }

            if (($repository_changed && $asset_edit['original']['issues_url'] === '') || $asset_edit['issues_url'] === '') {
                $asset_edit['issues_url'] = $container->utils->getDefaultIssuesUrl(
                    $asset_edit['browse_url'] ?: $asset_edit['original']['browse_url'],
                    $asset_edit['download_provider'] ?: $asset_edit['original']['download_provider']
                );
            }

            if ($asset_edit['godot_version'] !== null) {
                $asset_edit['godot_version'] = $container->utils->getFormattedGodotVersion((int) $asset_edit['godot_version'], $warning);
            }

            $asset_edit['original']['download_url'] = $container->utils->getComputedDownloadUrl($asset_edit['original']['browse_url'], $asset_edit['original']['download_provider'], $asset_edit['original']['download_commit']);

            if ($asset_edit['original']['issues_url'] === '') {
                $asset_edit['original']['issues_url'] = $container->utils->getDefaultIssuesUrl($asset_edit['original']['browse_url'], $asset_edit['original']['download_provider']);
            }
        } else {
            $asset_edit['download_url'] = $container->utils->getComputedDownloadUrl($asset_edit['browse_url'], $asset_edit['download_provider'], $asset_edit['download_commit'], $warning);
            $asset_edit['godot_version'] = $container->utils->getFormattedGodotVersion((int) $asset_edit['godot_version'], $warning);

            if ($asset_edit['issues_url'] === '') {
                $asset_edit['issues_url'] = $container->utils->getDefaultIssuesUrl($asset_edit['browse_url'], $asset_edit['download_provider']);
            }
        }

        if (($asset_edit['download_provider'] ?: $asset_edit['original']['download_provider']) !== 'Custom') {
            if ($asset_edit['download_commit'] === 'master') {
                $warning .= "Giving 'master' (or any other branch name) as the commit to be downloaded is not recommended, since it would invalidate the asset when you push a new version (since we ensure the version is kept the same via a sha256 hash of the zip). You can try using tags instead.\n";
            }
            if (sizeof(preg_grep('/\/|\\|\:|^\.|\ |\^|\~|\?|\*|\[|^\@$|\@\{/', [$asset_edit['download_commit']])) !== 0) {
                $warning .= "The inputted download commit is not a valid git ref, please ensure you aren't giving a full URL. (If your tag includes '/' in its name, consider escaping it as '%2F')\n";
            }
        }

        if ($warning !== null) {
            $asset_edit['warning'] = $warning;
        }


        return $response->withJson($asset_edit, 200);
    }

    public function createForNewAsset($request, $response)
    {
        $container = $this->container;
        $user = $request->getAttribute('user');

        $body = $request->getParsedBody();

        return $this->_submitAssetEdit($response, $body, $user['user_id'], -1);
    }

    public function createForExistingAsset($request, $response, $id)
    {
        $container = $this->container;
        $user = $request->getAttribute('user');

        $body = $request->getParsedBody();

        return $this->_submitAssetEdit($response, $body, $user['user_id'], (int) $id);
    }

    public function update($request, $response, $id)
    {
        $container = $this->container;

        $body = $request->getParsedBody();

        // Fetch the edit to check the user id
        $query_edit = $container->queries['asset_edit']['get_one_bare'];
        $query_edit->bindValue(':edit_id', (int) $id, \PDO::PARAM_INT);
        $query_edit->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_edit);
        $error = $container->utils->errorResponseIfQueryNoResults($error, $response, $query_edit);
        if ($error) {
            return $response;
        }

        $asset_edit = $query_edit->fetchAll()[0];

        $asset_edit['asset_id'] = (int) $asset_edit['asset_id'];

        if ((int) $asset_edit['status'] !== $container->constants['edit_status']['new']) {
            return $response->withJson([
                'error' => 'You are no longer allowed to update this asset edit, please make a new one',
            ], 403);
        }

        // Build query
        $query = $container->queries['asset_edit']['update'];
        $query->bindValue(':edit_id', (int) $id, \PDO::PARAM_INT);


        $asset = null;
        if ($asset_edit['asset_id'] !== -1) {
            $query_asset = $container->queries['asset']['get_one_bare'];
            $query_asset->bindValue(':asset_id', (int) $asset_edit['asset_id'], \PDO::PARAM_INT);
            $query_asset->execute();

            $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_asset);
            if ($error) {
                return $response;
            }

            $asset = $query_asset->fetchAll()[0];

            $error = $this->_insertAssetEditFields(false, $response, $query, $body, false, $asset);
            if ($error) {
                return $response;
            }
        } else {
            $error = $this->_insertAssetEditFields(false, $response, $query, $body, true, $asset_edit); // Edit of new asset, everything must be non-null
            if ($error) {
                return $response;
            }
        }


        // Make a transaction, so we can roll back failed submissions
        $container->db->beginTransaction();

        $query->execute();
        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            $container->db->rollback();
            return $response;
        }

        if (isset($body['previews'])) {
            $error = $this->_addPreviewsToEdit($error, $response, $id, $body['previews'], $asset, false);
            if ($error) {
                $container->db->rollback();
                return $response;
            }
        }

        $container->db->commit();

        return $response->withJson([
            'id' => $id,
            'url' => 'asset/edit/' . $id,
        ], 200);
    }

    public function accept($request, $response, $id)
    {
        $container = $this->container;

        $body = $request->getParsedBody();

        // Get the edit
        $query_edit = $container->queries['asset_edit']['get_one'];
        $query_edit->bindValue(':edit_id', (int) $id, \PDO::PARAM_INT);
        $query_edit->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_edit);
        $error = $container->utils->errorResponseIfQueryNoResults($error, $response, $query_edit);
        if ($error) {
            return $response;
        }

        $asset_edit_previews = $query_edit->fetchAll();
        $asset_edit = $asset_edit_previews[0];
        if ((int) $asset_edit['status'] !== $container->constants['edit_status']['in_review']) {
            return $response->withJson([
                'error' => 'The edit should be in review in order to be accepted',
            ], 403);
        }

        // Start building the query
        $query = null;

        if ((int) $asset_edit['asset_id'] === -1) {
            $query = $container->queries['asset']['apply_creational_edit'];
            $query->bindValue(':user_id', (int) $asset_edit['user_id'], \PDO::PARAM_INT);
        } else {
            $query = $container->queries['asset']['apply_edit'];
            $query->bindValue(':asset_id', (int) $asset_edit['asset_id'], \PDO::PARAM_INT);
        }

        // Params
        $update_version = false;
        foreach ($container->constants['asset_edit_fields'] as $i => $field) {
            if (isset($asset_edit[$field]) && $asset_edit[$field] !== null) {
                $query->bindValue(':' . $field, $asset_edit[$field]);
                $update_version = $update_version || ($field === 'browse_url' || $field === 'download_provider' || $field === 'download_commit' || $field === 'version_string');
            } else {
                $query->bindValue(':' . $field, null, \PDO::PARAM_NULL);
            }
        }

        if ($update_version) {
            $error = $container->utils->errorResponseIfMissingOrNotString(false, $response, $body, 'hash');
            if ($error) {
                return $response;
            }

            $body['hash'] = trim($body['hash']);
            if (sizeof(preg_grep('/^[a-f0-9]{64}$/', [$body['hash']])) === 0) {
                return $response->withJson([
                    'error' => 'Invalid hash given. Expected 64 lowercase hexadecimal digits.',
                ]);
            }

            $query->bindValue(':update_version', 1, \PDO::PARAM_INT);
            $query->bindValue(':download_hash', $body['hash']);
        } else {
            if (isset($body['hash']) && trim($body['hash']) !== '') {
                $body['hash'] = trim($body['hash']);
                if (sizeof(preg_grep('/^[a-f0-9]{64}$/', [$body['hash']])) === 0) {
                    return $response->withJson([
                        'error' => 'Invalid hash given. Expected either nothing or 64 lowercase hexadecimal digits.',
                    ]);
                }
                $query->bindValue(':update_version', 1, \PDO::PARAM_INT);
                $query->bindValue(':download_hash', $body['hash']);
            } else {
                $query->bindValue(':update_version', 0, \PDO::PARAM_INT);
                $query->bindValue(':download_hash', null, \PDO::PARAM_NULL);
            }
        }

        // Update the status to prevent race conditions
        $query_status = $container->queries['asset_edit']['set_status_and_reason'];

        $query_status->bindValue(':edit_id', (int) $id, \PDO::PARAM_INT);
        $query_status->bindValue(':status', (int) $container->constants['edit_status']['accepted'], \PDO::PARAM_INT);
        $query_status->bindValue(':reason', '');

        $query_status->execute();
        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_status);
        $error = $container->utils->errorResponseIfQueryNoResults(false, $response, $query_status); // Important: Ensure that something was actually changed
        if ($error) {
            return $response;
        }

        // Run
        $query->execute();
        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            return $response;
        }

        // Update the id in case it was newly-created
        if ((int) $asset_edit['asset_id'] === -1) {
            $asset_edit['asset_id'] = $container->db->lastInsertId();

            $query_update_id = $container->queries['asset_edit']['set_asset_id'];

            $query_update_id->bindValue(':edit_id', (int) $id, \PDO::PARAM_INT);
            $query_update_id->bindValue(':asset_id', (int) $asset_edit['asset_id'], \PDO::PARAM_INT);

            $query_update_id->execute();

            $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_update_id);
            if ($error) {
                return $response;
            }
            $query_update_id->closeCursor();
        }

        $previews_processed = [];
        foreach ($asset_edit_previews as $i => $preview) {
            if (!isset($preview['edit_preview_id']) || $preview['edit_preview_id'] === null || isset($previews_processed[$preview['edit_preview_id']])) {
                continue;
            }
            $previews_processed[$preview['edit_preview_id']] = true;
            $operation = $container->constants['edit_preview_operation'][$preview['operation']];
            $query_apply_preview = $container->queries['asset']['apply_preview_edit_' . $operation];

            $query_apply_preview->bindValue(':asset_id', (int) $asset_edit['asset_id']);

            if ($operation === 'remove' || $operation === 'update') {
                $query_apply_preview->bindValue(':preview_id', (int) $preview['preview_id']);
            }

            if ($operation === 'insert' || $operation === 'update') {
                foreach ($container->constants['asset_edit_preview_fields'] as $i => $field) {
                    if (isset($preview[$field])) {
                        $query_apply_preview->bindValue(':' . $field, $preview[$field]);
                    } else {
                        $query_apply_preview->bindValue(':' . $field, null, \PDO::PARAM_NULL);
                    }
                }
            }

            $query_apply_preview->execute();
            $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_apply_preview);
            if ($error) {
                return $response;
            }
        }

        return $response->withJson([
            'id' => $asset_edit['asset_id'],
            'url' => 'asset/' . $asset_edit['asset_id'],
        ], 200);
    }

    public function review($request, $response, $id)
    {
        $container = $this->container;

        $body = $request->getParsedBody();

        // Get the edit
        $query_edit = $container->queries['asset_edit']['get_one_bare'];
        $query_edit->bindValue(':edit_id', (int) $id, \PDO::PARAM_INT);
        $query_edit->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_edit);
        $error = $container->utils->errorResponseIfQueryNoResults($error, $response, $query_edit);
        if ($error) {
            return $response;
        }

        $asset_edit = $query_edit->fetchAll()[0];
        if ((int) $asset_edit['status'] > $container->constants['edit_status']['in_review']) {
            return $response->withJson([
                'error' => 'The edit should be new in order to be put in review',
            ], 403);
        }

        // Do the change
        $query = $container->queries['asset_edit']['set_status_and_reason'];

        $query->bindValue(':edit_id', (int) $id, \PDO::PARAM_INT);
        $query->bindValue(':status', (int) $container->constants['edit_status']['in_review'], \PDO::PARAM_INT);
        $query->bindValue(':reason', '');

        $query->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            return $response;
        }

        $asset_edit['status'] = 'in_review'; // Prepare to send
        $asset_edit['url'] = 'asset/edit/' . $id;

        return $response->withJson($asset_edit, 200);
    }

    public function reject($request, $response, $id)
    {
        $container = $this->container;

        $body = $request->getParsedBody();

        $error = $container->utils->errorResponseIfMissingOrNotString($error, $response, $body, 'reason');
        if ($error) {
            return $response;
        }

        $query = $container->queries['asset_edit']['set_status_and_reason'];

        $query->bindValue(':edit_id', (int) $id, \PDO::PARAM_INT);
        $query->bindValue(':status', (int) $container->constants['edit_status']['rejected'], \PDO::PARAM_INT);
        $query->bindValue(':reason', $body['reason']);

        $query->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            return $response;
        }

        return $response->withJson([
            'rejected' => true,
            'url' => 'asset/edit/' . $id,
        ], 200);
    }

    private function _submitAssetEdit($response, $body, $user_id, $asset_id = -1)
    {
        $container = $this->container;
        $has_changes = false;

        $query = $container->queries['asset_edit']['submit'];
        $query->bindValue(':user_id', $user_id, \PDO::PARAM_INT);
        $query->bindValue(':asset_id', $asset_id, \PDO::PARAM_INT);
        if ($asset_id === -1) {
            $error = $this->_insertAssetEditFields(false, $response, $query, $body, true, null);
            if ($error) {
                return $response;
            }
        } else {
            $query_asset = $container->queries['asset']['get_one_bare'];
            $query_asset->bindValue(':asset_id', (int) $asset_id, \PDO::PARAM_INT);
            $query_asset->execute();

            $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_asset);
            if ($error) {
                return $response;
            }

            $asset = $query_asset->fetchAll()[0];

            $error = $this->_insertAssetEditFields(false, $response, $query, $body, false, $asset, $has_changes);
            if ($error) {
                return $response;
            }
        }

        // Make a transaction, so we can roll back failed submissions
        $container->db->beginTransaction();

        $query->execute();
        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            $container->db->rollback();
            return $response;
        }

        $id = $container->db->lastInsertId();

        if (isset($body['previews'])) {
            $error = $this->_addPreviewsToEdit($error, $response, $id, $body['previews'], null, $asset_id === -1, $has_changes);

            if (!$has_changes && $asset_id !== -1) {
                $response = $response->withJson([
                    'error' => 'Creating edits without changes is not allowed.',
                ], 400);
                $error = true;
            }
            if ($error) {
                $container->db->rollback();
                return $response;
            }
        }

        $container->db->commit();

        return $response->withJson([
            'id' => $id,
            'url' => 'asset/edit/' . $id,
        ], 200);
    }

    private function _insertAssetEditFields($error, &$response, $query, $body, $required = false, $bare_asset = null, &$has_changes = false)
    {
        $container = $this->container;

        if ($error) {
            return true;
        }

        if (isset($body['download_provider'])) {
            if (isset($container->constants['download_provider'][$body['download_provider']])) {
                $body['download_provider'] = (string) ((int) $container->constants['download_provider'][$body['download_provider']]);
            } else {
                $body['download_provider'] = '0';
            }

            // if ($body['download_provider'] === $container->constants['download_provider']['Custom'] && ($bare_asset === null || $bare_asset['download_provider'] !== $body['download_provider'])) {
            //     $error = $container->utils->errorResponseIfNotUserHasLevel($error, $response, $user, 'moderator', 'You are not authorized to use the Custom provider');
            //     if ($error) {
            //         return $response;
            //     }
            // }
        }


        if (isset($body['issues_url'])) {
            $default_issues_url = null;
            if (isset($body['browse_url']) && isset($body['download_provider'])) {
                $default_issues_url = $container->utils->getDefaultIssuesUrl(
                    $body['browse_url'],
                    intval($body['download_provider'])
                );
            } elseif ($bare_asset !== null) {
                $default_issues_url = $container->utils->getDefaultIssuesUrl(
                    $body['browse_url'] ?: $bare_asset['browse_url'],
                    intval($body['download_provider'] ?: $bare_asset['download_provider'])
                );
            }
            if ($default_issues_url !== null && $default_issues_url === $body['issues_url']) {
                if ($bare_asset !== null && ($bare_asset['issues_url'] === $body['issues_url'] || $bare_asset['issues_url'] === '')) {
                    unset($body['issues_url']);
                } else {
                    $body['issues_url'] = '';
                }
            }
        }

        if (isset($body['godot_version'])) {
            $body['godot_version'] = (string) $container->utils->getUnformattedGodotVersion($body['godot_version']);
        }

        foreach ($container->constants['asset_edit_fields'] as $i => $field) {
            if (!$required) {
                if (isset($body[$field]) && ($bare_asset === null || $bare_asset[$field] !== $body[$field])) {
                    $has_changes = true;
                    $query->bindValue(':' . $field, $body[$field]);
                } else {
                    $query->bindValue(':' . $field, null, \PDO::PARAM_NULL);
                }
            } else {
                if ($bare_asset === null) {
                    if ($field === 'issues_url') { // Default value present, so, no need to error out
                        if (isset($body[$field])) {
                            $has_changes = true;
                            $query->bindValue(':' . $field, $body[$field]);
                        } else {
                            $query->bindValue(':' . $field, '', \PDO::PARAM_NULL);
                        }
                    } else {
                        $error = $container->utils->errorResponseIfMissingOrNotString($error, $response, $body, $field);
                        if (!$error) {
                            $query->bindValue(':' . $field, $body[$field]);
                        }
                    }
                } else { // "Required" (so, non-null), but there is a base asset, so we can support incremental changes
                    if (isset($body[$field])) {
                        $has_changes = true;
                        $query->bindValue(':' . $field, $body[$field]);
                    } else {
                        $query->bindValue(':' . $field, $bare_asset[$field]);
                    }
                }
            }

            if ($error) {
                return $error;
            }
        }

        return $error;
    }

    private function _addPreviewsToEdit($error, &$response, $edit_id, $previews, $asset = null, $required = false, &$has_changes = false)
    {
        $container = $this->container;
        if ($error) {
            return true;
        }

        foreach ($previews as $i => $preview) {
            if (!isset($preview['enabled']) || !$preview['enabled']) {
                continue;
            }
            if ($required || !isset($preview['edit_preview_id'])) {
                $has_changes = true;
                $query = $container->queries['asset_edit']['add_preview'];

                $error = $container->utils->errorResponseIfMissingOrNotString($error, $response, $preview, 'operation');
                if ($error) {
                    return $error;
                }

                $operation = $container->constants['edit_preview_operation']['insert'];

                if (!$required && $asset !== null && isset($container->constants['edit_preview_operation'][$preview['operation']])) {
                    $operation = $container->constants['edit_preview_operation'][$preview['operation']];
                }
                $query->bindValue(':operation', (int) $operation, \PDO::PARAM_INT);

                if ($operation === $container->constants['edit_preview_operation']['insert']) {
                    $query->bindValue(':preview_id', -1, \PDO::PARAM_INT);
                } else {
                    $error = $container->utils->errorResponseIfMissingOrNotString($error, $response, $preview, 'preview_id');
                    if ($error) {
                        return $error;
                    }

                    if ($asset !== null) {
                        $query_check = $container->queries['asset']['get_one_preview_bare'];
                        $query_check->bindValue(':preview_id', (int) $preview['preview_id'], \PDO::PARAM_INT);
                        $query_check->execute();
                        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_check);
                        $error = $container->utils->errorResponseIfQueryNoResults($error, $response, $query_check);
                        if ($error) {
                            return $error;
                        }

                        $original_preview = $query_check->fetchAll()[0];
                        if ($original_preview['asset_id'] !== $asset['asset_id']) {
                            $response = $response->withJson(['error' => 'Invalid preview id.'], 400);
                            return true;
                        }
                    } else {
                        $response = $response->withJson(['error' => 'Bug in processing code, please report.'], 500);
                        return true;
                    }

                    $query->bindValue(':preview_id', (int) $preview['preview_id'], \PDO::PARAM_INT);
                }
            } elseif (isset($preview['remove']) && $preview['remove']) {
                $has_changes = true;
                $query = $container->queries['asset_edit']['remove_preview'];
                $query->bindValue(':edit_preview_id', (int) $preview['edit_preview_id'], \PDO::PARAM_INT);
                $query->bindValue(':edit_id', (int) $edit_id, \PDO::PARAM_INT);
                $query->execute();
                $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
                if ($error) {
                    return $error;
                }

                continue;
            } else {
                $query = $container->queries['asset_edit']['update_preview'];
                $error = $container->utils->errorResponseIfMissingOrNotString($error, $response, $preview, 'edit_preview_id');
                if ($error) {
                    return $error;
                }
                $query->bindValue(':edit_preview_id', (int) $preview['edit_preview_id'], \PDO::PARAM_INT);
            }
            $query->bindValue(':edit_id', (int) $edit_id, \PDO::PARAM_INT);

            foreach ($container->constants['asset_edit_preview_fields'] as $i => $field) {
                if (!$required) {
                    if (isset($preview[$field]) && !(isset($original_preview) && $original_preview[$field] === $preview[$field])) {
                        $has_changes = true;
                        $query->bindValue(':' . $field, $preview[$field]);
                    } elseif (!isset($preview[$field]) && !isset($original_preview)) {
                        $query->bindValue(':' . $field, $preview[$field]);
                    } else {
                        $query->bindValue(':' . $field, null, \PDO::PARAM_NULL);
                    }
                } else {
                    $error = $container->utils->errorResponseIfMissingOrNotString($error, $response, $preview, $field);
                    if (!$error) {
                        $query->bindValue(':' . $field, $preview[$field]);
                    }
                }
            }
            if ($error) {
                return $error;
            }

            $query->execute();
            $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
            if ($error) {
                return $error;
            }
        }

        return $error;
    }
}
