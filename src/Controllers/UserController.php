<?php

namespace Godot\AssetLibrary\Controllers;

class UserController
{
    private $container;

    public function __construct($container) // TODO: Passing the container directly to serve as a service locator is discouraged
    {
        $this->container = $container;
    }

    public function getFeed($request, $response)
    {
        $container = $this->container;
        $user = $request->getAttribute('user');

        $page_size = 40;
        $max_page_size = 500;
        $page_offset = 0;
        if (isset($params['max_results'])) {
            $page_size = min(abs((int) $params['max_results']), $max_page_size);
        }
        if (isset($params['page'])) {
            $page_offset = abs((int) $params['page']) * $page_size;
        } elseif (isset($params['offset'])) {
            $page_offset = abs((int) $params['offset']);
        }

        $query = $container->queries['user']['list_edit_events'];
        $query->bindValue(':user_id', (int) $user['user_id'], \PDO::PARAM_INT);
        $query->bindValue(':page_size', $page_size, \PDO::PARAM_INT);
        $query->bindValue(':skip_count', $page_offset, \PDO::PARAM_INT);
        $query->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            return $response;
        }

        $events = $query->fetchAll();

        $context = $container;
        $events = array_map(function ($event) use ($context) {
            $event['status'] = $context->constants['edit_status'][(int) $event['status']];
            return $event;
        }, $events);

        return $response->withJson([
            'events' => $events,
        ], 200);
    }
}
