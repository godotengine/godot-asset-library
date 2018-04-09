<?php
// Auth routes


namespace Godot\AssetLibrary\Controllers;

class AuthController
{
    private $container;

    public function __construct($container) // TODO: Passing the container directly to serve as a service locator is discouraged
    {
        $this->container = $container;
    }

    public function configure($request, $response)
    {
        $container = $this->container;

        $params = $request->getQueryParams();

        $category_type = $container->constants['category_type']['addon'];

        if (isset($params['type']) && isset($container->constants['category_type'][$params['type']])) {
            $category_type = $container->constants['category_type'][$params['type']];
        }

        $query = $container->queries['category']['list'];
        $query->bindValue(':category_type', $category_type);
        $query->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            return $response;
        }

        if (isset($request->getQueryParams()['session'])) {
            $id = openssl_random_pseudo_bytes($container->settings['auth']['tokenSessionBytesLength']);
            $token = $container->tokens->generate([
                'session' => base64_encode($id),
            ]);

            return $response->withJson([
                'categories' => $query->fetchAll(),
                'token' => $token,
                'login_url' => $_SERVER['HTTP_HOST'] .
                    (FRONTEND ? dirname($request->getUri()->getBasePath()) : $request->getUri()->getBasePath()) .
                    '/login#' . urlencode($token),
                // ^ TODO: Make those routes actually work
            ], 200);
        } else {
            return $response->withJson([
                'categories' => $query->fetchAll(),
            ], 200);
        }
    }

    public function register($request, $response)
    {
        $container = $this->container;

        $body = $request->getParsedBody();
        $query = $container->queries['user']['register'];
        $query_check = $container->queries['user']['get_by_username'];

        $error = $container->utils->errorResponseIfMissingOrNotString(false, $response, $body, 'username');
        $error = $container->utils->errorResponseIfMissingOrNotString($error, $response, $body, 'email');
        $error = $container->utils->errorResponseIfMissingOrNotString($error, $response, $body, 'password');
        if ($error) {
            return $response;
        }

        $query_check->bindValue(':username', $body['username']);
        $query_check->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_check);
        if ($error) {
            return $response;
        }

        if ($query_check->rowCount() > 0) {
            return $response->withJson([
                'error' => 'Username already taken.',
            ], 409);
        }

        $password_hash = password_hash($body['password'], PASSWORD_BCRYPT, $container->get('settings')['auth']['bcryptOptions']);

        $query->bindValue(':username', $body['username']);
        $query->bindValue(':email', $body['email']); // TODO: Verify email.
        $query->bindValue(':password_hash', $password_hash);

        $query->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            return $response;
        }

        return $response->withJson([
            'username' => $body['username'],
            'registered' => true,
            'url' => 'login',
        ], 200);
    }

    public function login($request, $response)
    {
        $container = $this->container;

        $body = $request->getParsedBody();
        $query = $container->queries['user']['get_by_username'];

        $error = $container->utils->errorResponseIfMissingOrNotString(false, $response, $body, 'username');
        $error = $container->utils->errorResponseIfMissingOrNotString($error, $response, $body, 'password');
        if ($error) {
            return $response;
        }

        $query->bindValue(':username', $body['username']);
        $query->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        $error = $container->utils->errorResponseIfQueryNoResults(false, $response, $query, 'No such username: ' . $body['username']);
        if ($error) {
            return $response;
        }

        $user = $query->fetchAll()[0];

        if (password_verify($body['password'], $user['password_hash'])) {
            if (isset($body['authorize_token'])) {
                $token_data = $container->tokens->validate($body['authorize_token']);

                if (!$token_data || !isset($token_data->session)) {
                    return $response->withJson([
                       'error' => 'Invalid token supplied'
                   ], 400);
                }

                $session_id = $token_data->session;
                $token = $body['authorize_token'];
            } else {
                $session_id = openssl_random_pseudo_bytes($container->settings['auth']['tokenSessionBytesLength']);
                $token = $container->tokens->generate([
                   'session' => base64_encode($session_id),
               ]);
            }

            $query_session = $container->queries['user']['set_session_token'];
            $query_session->bindValue(':id', (int) $user['user_id'], \PDO::PARAM_INT);
            $query_session->bindValue(':session_token', $session_id);
            $query_session->execute();
            $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_session);
            if ($error) {
                return $response;
            }

            return $response->withJson([
               'username' => $body['username'],
               'token' => $token,
               'authenticated' => true,
               'url' => 'asset',
           ], 200);
        } else {
            return $response->withJson([
               'authenticated' => false,
               'error' => 'Password doesn\'t match',
           ], 403);
        }
    }

    public function logout($request, $response)
    {
        $container = $this->container;
        $user = $request->getAttribute('user');

        $query = $container->queries['user']['set_session_token'];
        $query->bindValue(':id', (int) $user['user_id'], \PDO::PARAM_INT);
        $query->bindValue(':session_token', null, \PDO::PARAM_NULL);
        $query->execute();

        return $response->withJson([
           'authenticated' => false,
           'token' => '',
           'url' => 'login',
       ], 200);
    }

    public function sendResetPasswordEmail($request, $response)
    {
        $container = $this->container;

        $body = $request->getParsedBody();

        $error = $container->utils->errorResponseIfMissingOrNotString(false, $response, $body, 'email');
        if ($error) {
            return $response;
        }

        $query_user = $container->queries['user']['get_by_email'];
        $query_user->bindValue(':email', $body['email']);
        $query_user->execute();

        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_user);
        if ($error) {
            return $response;
        }


        if ($query_user->rowCount() != 0) {
            $user = $query_user->fetchAll()[0];

            $reset_id = openssl_random_pseudo_bytes($container->settings['auth']['tokenResetBytesLength']);
            $token = $container->tokens->generate([
               'reset' => base64_encode($reset_id),
           ]);

            $query = $container->queries['user']['set_reset_token'];
            $query->bindValue(':id', (int) $user['user_id'], \PDO::PARAM_INT);
            $query->bindValue(':reset_token', $reset_id);
            $query->execute();
            $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
            if ($error) {
                return $response;
            }

            $reset_link = $request->getUri()->getScheme() . '://' . $_SERVER['HTTP_HOST'] .
                (FRONTEND ? $request->getUri()->getBasePath() : dirname($request->getUri()->getBasePath())) .
                '/reset_password?token=' . urlencode($token);

            $mail = $container->mail->__invoke(); // Since its a function closure, we have to invoke it with magic methods
            $mail->addAddress($user['email'], $user['username']);
            $mail->isHTML(true);
            $mail->Subject = "Password reset requested for $user[username]";
            $mail->Body = $container->renderer->fetch('reset_password_email.phtml', [
                'user' => $user,
                'link' => $reset_link,
            ]);
            $mail->AltBody = "Reset your ($user[username]'s) password: $reset_link\n";
            if (!$mail->send()) {
                $container->logger->error('mailSendFail', [$mail->ErrorInfo]);
            }
            // $container->logger->info('mailLinkDebug', [$reset_link]);
        } else {
            sleep(1);
        }

        return $response->withJson([
           'email' => $body['email'],
       ], 200);
    }

    public function temporaryResetLogin($request, $response)
    {
        $container = $this->container;

        $params = $request->getQueryParams();
        $body = null !== $request->getParsedBody() ? $request->getParsedBody() : [];

        $combined_body = $params + $body;

        $error = $container->utils->ensureLoggedIn(false, $response, $combined_body, $user, $token_data, true);
        if ($error) {
            return $response;
        }

        return $response->withJson([
           'token' => $combined_body['token'],
       ], 200);
    }

    public function resetPassword($request, $response)
    {
        $container = $this->container;
        $user = $request->getAttribute('user');

        $body = $request->getParsedBody();

        $error = $container->utils->ensureLoggedIn(false, $response, $body, $user, $token_data, true);
        $error = $container->utils->errorResponseIfMissingOrNotString($error, $response, $body, 'password');
        if ($error) {
            return $response;
        }

        $password_hash = password_hash($body['password'], PASSWORD_BCRYPT, $container->get('settings')['auth']['bcryptOptions']);

        $query_password = $container->queries['user']['set_password_and_nullify_session'];
        $query_password->bindValue(':id', (int) $user['user_id'], \PDO::PARAM_INT);
        $query_password->bindValue(':password_hash', $password_hash);
        $query_password->execute();
        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_password);
        if ($error) {
            return $response;
        }

        $query = $container->queries['user']['set_reset_token'];
        $query->bindValue(':id', (int) $user['user_id'], \PDO::PARAM_INT);
        $query->bindValue(':reset_token', null, \PDO::PARAM_NULL);
        $query->execute();
        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query);
        if ($error) {
            return $response;
        }

        return $response->withJson([
            'token' => null,
            'url' => 'login',
        ], 200);
    }

    public function changePassword($request, $response)
    {
        $container = $this->container;

        $body = $request->getParsedBody();

        $error = $container->utils->errorResponseIfMissingOrNotString(false, $response, $body, 'new_password');
        $error = $container->utils->errorResponseIfMissingOrNotString($error, $response, $body, 'old_password');
        if ($error) {
            return $response;
        }

        if (!password_verify($body['old_password'], $user['password_hash'])) {
            return $response->withJson([
                'error' => 'Wrong old password supplied!',
            ], 403);
        }

        $password_hash = password_hash($body['new_password'], PASSWORD_BCRYPT, $container->get('settings')['auth']['bcryptOptions']);

        $query_password = $container->queries['user']['set_password_and_nullify_session'];
        $query_password->bindValue(':id', (int) $user['user_id'], \PDO::PARAM_INT);
        $query_password->bindValue(':password_hash', $password_hash);
        $query_password->execute();
        $error = $container->utils->errorResponseIfQueryBad(false, $response, $query_password);
        if ($error) {
            return $response;
        }

        return $response->withJson([
            'token' => null,
            'url' => 'login',
        ], 200);
    }
}
