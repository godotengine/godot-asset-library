<?php

namespace Godot\AssetLibrary\Controllers;

class AssetController
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

        // Defaults

        $category = '%';
        $filter = '%';
        $username = '%';
        $order_column = 'modify_date';
        $order_direction = 'desc';
        $support_levels = [];
        $page_size = 10;
        $max_page_size = 500;
        $page_offset = 0;
        $min_godot_version = 0;
        $max_godot_version = 9999999;
        $category_type = $container->constants['category_type']['any'];

        if (!FRONTEND) {
            $category_type = $container->constants['category_type']['addon'];
            $min_godot_version = 20100;
            $max_godot_version = 20199;
        }

        // Parameter parsing
        if (isset($params['category']) && $params['category'] !== '') {
            $category = (int) $params['category'];
        }
        if (isset($params['type']) && isset($container->constants['category_type'][$params['type']])) {
            $category_type = $container->constants['category_type'][$params['type']];
        }
        if (isset($params['support'])) { // Expects the param to be like `support=community+testing` or `support[community]=1&support[testing]=1&...`
            $support_levels = [];
            if (is_array($params['support'])) {
                foreach ($params['support'] as $key => $value) {
                    if ($value && isset($container->constants['support_level'][$key])) {
                        array_push($support_levels, (int) $container->constants['support_level'][$key]);
                    }
                }
            } else {
                foreach (explode(' ', $params['support']) as $key => $value) { // `+` is changed to ` ` automatically
                    if (isset($container->constants['support_level'][$value])) {
                        array_push($support_levels, (int) $container->constants['support_level'][$value]);
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
        if (isset($params['godot_version']) && $params['godot_version'] !== '') {
            if ($params['godot_version'] === 'any') {
                $min_godot_version = 0;
                $max_godot_version = 9999999;
            } else {
                $godot_version = $container->utils->getUnformattedGodotVersion($params['godot_version']);
                $min_godot_version = floor($godot_version / 10000) * 10000; // Keep just the major version
                $max_godot_version = $godot_version; // Assume version requested can't handle future patches
                // $max_godot_version = floor($godot_version / 100) * 100 + 99; // Assume future patches will work
            }
        }
        if (isset($params['page'])) {
            $page_offset = abs((int) $params['page']) * $page_size;
        } elseif (isset($params['offset'])) {
            $page_offset = abs((int) $params['offset']);
        }
        if (isset($params['sort'])) {
            $column_mapping = [
                'rating' => 'rating',
                'cost' => 'cost',
                'name' => 'title',
                'updated' => 'modify_date'
                // TODO: downloads
            ];
            if (isset($column_mapping[$params['sort']])) {
                $order_column = $column_mapping[$params['sort']];
            }
        }
        if (isset($params['reverse'])) {
            $order_direction = 'asc';
        }

        if (count($support_levels) === 0) {
            $support_levels = [0, 1, 2]; // Testing + Community + Official
        }
        $support_levels = implode('|', $support_levels);

        $query = $container->queries['asset']['search'];
        $query->bindValue(':category', $category);
        $query->bindValue(':category_type', $category_type);
        $query->bindValue(':min_godot_version', $min_godot_version, \PDO::PARAM_INT);
        $query->bindValue(':max_godot_version', $max_godot_version, \PDO::PARAM_INT);
        $query->bindValue(':support_levels_regex', $support_levels);
        $query->bindValue(':filter', $filter);
        $query->bindValue(':username', $username);
        $query->bindValue(':order', $order_column);
        $query->bindValue(':order_direction', $order_direction);
        $query->bindValue(':page_size', $page_size, \PDO::PARAM_INT);
        $query->bindValue(':skip_count', $page_offset, \PDO::PARAM_INT);
        $query->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            return $response;
        }

        $query_count = $container->queries['asset']['search_count'];
        $query_count->bindValue(':category', $category);
        $query_count->bindValue(':category_type', $category_type);
        $query_count->bindValue(':min_godot_version', $min_godot_version, \PDO::PARAM_INT);
        $query_count->bindValue(':max_godot_version', $max_godot_version, \PDO::PARAM_INT);
        $query_count->bindValue(':support_levels_regex', $support_levels);
        $query_count->bindValue(':filter', $filter);
        $query_count->bindValue(':username', $username);
        $query_count->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_count);
        if ($error) {
            return $response;
        }

        $total_count = $query_count->fetchAll()[0]['count'];

        $assets = $query->fetchAll();

        $assets = array_map(function ($asset) use ($container) {
            $asset['godot_version'] = $container->utils->getFormattedGodotVersion((int) $asset['godot_version']);
            $asset['support_level'] = $container->constants['support_level'][(int) $asset['support_level']];
            return $asset;
        }, $assets);

        return $response->withJson([
            'result' => $assets,
            'page' => floor($page_offset / $page_size),
            'pages' => ceil($total_count / $page_size),
            'page_length' => $page_size,
            'total_items' => (int) $total_count,
        ], 200);
    }

    public function getOne($request, $response, $id)
    {
        $container = $this->container;

        $query = $container->queries['asset']['get_one'];

        $query->bindValue(':id', $id, \PDO::PARAM_INT);
        $query->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            return $response;
        }

        if ($query->rowCount() <= 0) {
            return $response->withJson([
                'error' => 'Couldn\'t find asset with id '.$id.'!'
            ], 404);
        }

        $output = $query->fetchAll();
        $asset_info = [];
        $previews = [];

        foreach ($output as $row) {
            foreach ($row as $column => $value) {
                if ($value !== null) {
                    if ($column === 'preview_id') {
                        $previews[] = ['preview_id' => $value];
                    } elseif ($column === 'type' || $column === 'link' || $column === 'thumbnail') {
                        $previews[count($previews) - 1][$column] = $value;
                    } elseif ($column === 'category_type') {
                        $asset_info['type'] = $container->constants['category_type'][(int) $value];
                    } elseif ($column === 'support_level') {
                        $asset_info['support_level'] = $container->constants['support_level'][(int) $value];
                    } elseif ($column === 'download_provider') {
                        $asset_info['download_provider'] = $container->constants['download_provider'][(int) $value];
                    } elseif ($column === 'godot_version') {
                        $asset_info['godot_version'] = $container->utils->getFormattedGodotVersion((int) $value);
                    } else {
                        $asset_info[$column] = $value;
                    }
                }
            }
        }

        $asset_info['download_url'] = $container->utils->getComputedDownloadUrl($asset_info['browse_url'], $asset_info['download_provider'], $asset_info['download_commit']);
        if ($asset_info['issues_url'] === '') {
            $asset_info['issues_url'] = $container->utils->getDefaultIssuesUrl($asset_info['browse_url'], $asset_info['download_provider']);
        }


        foreach ($previews as $i => $_) {
            if (!isset($previews[$i]['thumbnail']) || $previews[$i]['thumbnail'] === '') {
                if ($previews[$i]['type'] === 'video') {
                    $matches = [];
                    if (preg_match('|youtube.com/watch\\?v=([^&]+)|', $previews[$i]['link'], $matches)) {
                        $previews[$i]['thumbnail'] = 'http://img.youtube.com/vi/'.$matches[1].'/default.jpg';
                    }
                } else {
                    $previews[$i]['thumbnail'] = $previews[$i]['link'];
                }
            }
        }

        $asset_info['previews'] = $previews;

        return $response->withJson($asset_info, 200);
    }

    public function changeSupportLevel($request, $response, $id)
    {
        $container = $this->container;

        $body = $request->getParsedBody();

        $error = $container->utils->errorResponseIfMissingOrNotString(false, $response, $body, 'support_level');
        if ($error) {
            return $response;
        }

        if (!isset($container->constants['support_level'][$body['support_level']])) {
            $numeric_value_keys = [];
            foreach ($container->constants['support_level'] as $key => $value) {
                if ((int) $value === $value) {
                    array_push($numeric_value_keys, $key);
                }
            }
            return $response->withJson([
                'error' => 'Invalid support level submitted, allowed are \'' . implode('\', \'', $numeric_value_keys) . '\'',
            ]);
        }

        $query = $container->queries['asset']['set_support_level'];

        $query->bindValue(':asset_id', (int) $id, \PDO::PARAM_INT);
        $query->bindValue(':support_level', (int) $container->constants['support_level'][$body['support_level']], \PDO::PARAM_INT);

        $query->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            return $response;
        }

        return $response->withJson([
            'changed' => true,
            'url' => 'asset/' . $id,
        ], 200);
    }

    public function softDelete($request, $response, $id)
    {
        $container = $this->container;

        $query = $container->queries['asset']['delete'];
        $query->bindValue(':asset_id', (int) $id, \PDO::PARAM_INT);
        $query->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            return $response;
        }

        return $response->withJson([
            'changed' => true,
            'url' => 'asset/' . $id,
        ], 200);
    }

    public function softUndelete($request, $response, $id)
    {
        $container = $this->container;

        $query = $container->queries['asset']['undelete'];
        $query->bindValue(':asset_id', (int) $id, \PDO::PARAM_INT);
        $query->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            return $response;
        }

        return $response->withJson([
            'changed' => true,
            'url' => 'asset/' . $id,
        ], 200);
    }
}
