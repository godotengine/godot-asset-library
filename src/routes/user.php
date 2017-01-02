<?php

// Feed
$get_feed = function ($request, $response, $args) {
    $body = $request->getParsedBody();

    $error = $this->utils->ensureLoggedIn(false, $response, $body, $user);
    if ($error) {
        return $response;
    }

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

    $query = $this->queries['user']['list_edit_events'];
    $query->bindValue(':user_id', (int) $user['user_id'], PDO::PARAM_INT);
    $query->bindValue(':page_size', $page_size, PDO::PARAM_INT);
    $query->bindValue(':skip_count', $page_offset, PDO::PARAM_INT);
    $query->execute();

    $error = $this->utils->errorResponseIfQueryBad(false, $response, $query);
    if ($error) {
        return $response;
    }

    $events = $query->fetchAll();

    $context = $this;
    $events = array_map(function ($event) use ($context) {
        $event['status'] = $context->constants['edit_status'][(int) $event['status']];
        return $event;
    }, $events);

    return $response->withJson([
        'events' => $events,
    ], 200);
};

// Binding to multiple routes
$app->post('/user/feed', $get_feed);
if (FRONTEND) {
    $app->get('/user/feed', $get_feed);
}
