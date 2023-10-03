<?php

namespace Godot\AssetLibrary\Helpers;

class Utils
{
    private $c;

    public function __construct($c)
    {
        $this->c = $c;
    }

    public function getComputedDownloadUrl($repo_url, $provider, $commit, &$warning=null, &$light_warning=null) // i.e. browse_url, download_provider, download_commit
    {
        $repo_url = rtrim($repo_url, '/');
        if (is_int($provider)) {
            $provider = $this->c->constants['download_provider'][$provider];
        }
        $warning_suffix = "Please, ensure that the URL and the repository provider are correct.";
        $light_warning_suffix = "Please, doublecheck that the URL and the repository provider are correct.";
        if (sizeof(preg_grep('/^https:\/\/.+\.git$/i', [$repo_url])) != 0) {
            $warning[] = "\"$repo_url\" doesn't look correct; it probably shouldn't end in .git. $warning_suffix";
        }
        if ($provider != 'Custom') {
            // Git commits are either 40 (SHA1) or 64 (SHA2) hex characters
            if (sizeof(preg_grep('/^[a-f0-9]{40}([a-f0-9]{24})?$/', [$commit])) == 0) {
                $warning[] = "Using Git tags or branches is no longer supported. Please give a full Git commit hash instead, or use the Custom download provider for GitHub Releases downloads.\n";
            }
        }
        switch ($provider) {
            case 'GitHub':
                if (sizeof(preg_grep('/^https:\/\/github\.com\/[^\/]+?\/[^\/]+?$/i', [$repo_url])) == 0) {
                    $warning[] = "\"$repo_url\" doesn't look correct; it should be similar to \"https://github.com/<owner>/<name>\". $warning_suffix";
                }
                return "$repo_url/archive/$commit.zip";
            case 'GitLab':
                if (sizeof(preg_grep('/^https:\/\/(gitlab\.com|[^\/]+)\/[^\/]+?\/[^\/]+?(?:[^\/]+\/?)*$/i', [$repo_url])) == 0) {
                    $warning[] = "\"$repo_url\" doesn't look correct; it should be similar to \"https://<gitlab instance>/<owner>/<optional groups>/<name>\". $warning_suffix";
                } elseif (sizeof(preg_grep('/^https:\/\/(gitlab\.com)\/[^\/]+?\/[^\/]+?(?:[^\/]+\/?)*$/i', [$repo_url])) == 0) {
                    $light_warning[] = "\"$repo_url\" might not be correct; it should be similar to \"https://gitlab.com/<owner>/<optional groups>/<name>\", unless the asset is hosted on a custom instance of GitLab. $light_warning_suffix";
                }
                return "$repo_url/-/archive/$commit.zip";
            case 'BitBucket':
                if (sizeof(preg_grep('/^https:\/\/bitbucket\.org\/[^\/]+?\/[^\/]+?$/i', [$repo_url])) == 0) {
                    $warning[] = "\"$repo_url\" doesn't look correct; it should be similar to \"https://bitbucket.org/<owner>/<name>\". $warning_suffix";
                }
                return "$repo_url/get/$commit.zip";
            case 'Gogs/Gitea/Codeberg':
                if (sizeof(preg_grep('/^https?:\/\/[^\/]+?\/[^\/]+?\/[^\/]+?$/i', [$repo_url])) == 0) {
                    $warning[] = "\"$repo_url\" doesn't look correct; it should be similar to \"http<s>://<gogs instance>/<owner>/<name>\". $warning_suffix";
                }
                if (sizeof(preg_grep('/^https:\/\/(notabug\.org|codeberg\.org)\/[^\/]+?\/[^\/]+?$/i', [$repo_url])) == 0) {
                    $light_warning[] = "Since Gogs/Gitea/Codeberg might be self-hosted, we can't be sure that \"$repo_url\" is a valid repository URL. $light_warning_suffix";
                }
                return "$repo_url/archive/$commit.zip";
            case 'cgit':
                if (sizeof(preg_grep('/^https?:\/\/[^\/]+?\/[^\/]+?\/[^\/]+?$/i', [$repo_url])) == 0) {
                    $warning[] = "\"$repo_url\" doesn't look correct; it should be similar to \"http<s>://<cgit instance>/<owner>/<name>\". $warning_suffix";
                }
                $light_warning[] = "Since cgit might be self-hosted, we can't be sure that \"$repo_url\" is a valid cgit URL. $light_warning_suffix";
                return "$repo_url/snapshot/$commit.zip";
            case 'Custom':
                if (sizeof(preg_grep('/^https?:\/\/.+?\.zip$/i', [$commit])) == 0) {
                    $warning[] = "\"$commit\" doesn't look correct; it should be similar to \"http<s>://<url>.zip\". $warning_suffix";
                }
                return "$commit";
            default:
                return "$repo_url/$commit.zip"; // Obviously incorrect, but we would like to have some default case...
        }
    }

    public function getDefaultIssuesUrl($repo_url, $provider) // i.e. browse_url, download_provider
    {
        $repo_url = rtrim($repo_url, '/');
        if (is_int($provider)) {
            $provider = $this->c->constants['download_provider'][$provider];
        }
        switch ($provider) {
            case 'GitHub':
            case 'GitLab':
            case 'BitBucket':
            case 'Gogs/Gitea/Codeberg':
                return "$repo_url/issues";
            case 'cgit':
            default:
                return "";
        }
    }

    public function getFormattedGodotVersion($internal_id, &$warning=null, &$light_warning=null)
    {
        if ($internal_id == $this->c->constants['special_godot_versions']['unknown']) {
            $light_warning[] = "Setting Godot version as \"unknown\" is not recommended, as it would prevent people from finding your asset easily.";
        }
        if (isset($this->c->constants['special_godot_versions'][$internal_id])) {
            return $this->c->constants['special_godot_versions'][$internal_id];
        } else {
            $major = floor($internal_id / 10000) % 100;
            $minor = floor($internal_id / 100) % 100;
            $patch = floor($internal_id / 1) % 100;
            if ($patch != 0) {
                return $major . '.' . $minor . '.' . $patch;
            } else {
                return $major . '.' . $minor;
            }
        }
    }

    public function getUnformattedGodotVersion($value)
    {
        if (is_int($value)) {
            return $value;
        }
        if (isset($this->c->constants['special_godot_versions'][$value])) {
            return $this->c->constants['special_godot_versions'][$value];
        } else {
            $slices = explode('.', $value);
            $major = (int) $slices[0];
            $minor = min(100, max(0, (int) (isset($slices[1]) ? $slices[1] : 0)));
            $patch = min(100, max(0, (int) (isset($slices[2]) ? $slices[2] : 0)));
            return $major * 10000 + $minor * 100 + $patch;
        }
    }

    public function errorResponseIfNotUserHasLevel($currentStatus, &$response, $user, $required_level_name, $message = 'You are not authorized to do this')
    {
        if ($user === false || $currentStatus) {
            return true;
        }

        if ((int) $user['type'] < $this->c->constants['user_type'][$required_level_name]) {
            $response = $response->withJson([
                'error' => $message,
            ], 403);
            return true;
        }
        return false;
    }

    public function errorResponseIfNotOwnerOrLevel($currentStatus, &$response, $user, $asset_id, $required_level_name, $message = 'You are not authorized to do this')
    {
        if($user === false || $currentStatus) {
            return true;
        }

        $query = $this->c->queries['asset']['get_one'];
        $query->bindValue(':id', (int) $asset_id, \PDO::PARAM_INT);
        $query->execute();

        if($query->rowCount() <= 0) {
            return $response->withJson(['error' => 'Couldn\'t find asset with id '.$asset_id.'!'], 404);
        }

        $asset = $query->fetch();

        if($asset['author_id'] != $user['user_id']) {
            return $this->errorResponseIfNotUserHasLevel(false, $response, $user, $required_level_name, $message);
        }

        return false;
    }

    public function errorResponseIfMissingOrNotString($currentStatus, &$response, $object, $property)
    {
        if ($currentStatus) {
            return true;
        }

        if (!isset($object[$property]) || !is_string($object[$property]) || $object[$property] == "") {
            $response = $response->withJson([
                'error' => $property . ' is required, and must be a string'
            ], 400);
            return true;
        }
        return false;
    }

    public function errorResponseIfQueryBad($currentStatus, &$response, $query, $message = 'An error occured while executing DB queries')
    {
        if ($currentStatus) {
            return true;
        }

        if ($query->errorCode() != '00000') {
            $this->c->logger->error('DBError', $query->errorInfo());
            $response = $response->withJson([
                'error' => $message,
            ], 500);
            return true;
        }
        return false;
    }

    public function errorResponseIfQueryNoResults($currentStatus, &$response, $query, $message = 'DB returned no results')
    {
        if ($currentStatus) {
            return true;
        }

        if ($query->rowCount() == 0) {
            $response = $response->withJson([
                'error' => $message
            ], 404);
            return true;
        }
        return false;
    }

    public function ensureLoggedIn($currentStatus, &$response, $body, &$user, &$token_data = null, $reset = false)
    {
        $currentStatus = $this->errorResponseIfMissingOrNotString($currentStatus, $response, $body, 'token');
        if ($currentStatus) {
            return true;
        }

        $token_data = $this->c->tokens->validate($body['token']);
        $error = $this->getUserFromTokenData(false, $response, $token_data, $user, $reset);
        return $error;
    }

    public function getUserFromTokenData($currentStatus, &$response, $token_data, &$user, $reset = false)
    {
        if ($currentStatus) {
            return true;
        }
        if (!$token_data) {
            $response = $response->withJson([
                'error' => 'Invalid token'
            ], 403);
            return true;
        }

        // Insecure
        // if(isset($token_data->user_id)) {
        //   $query = $this->c->queries['user']['get_one'];
        //   $query->bindValue(':id', (int) $token_data->user_id, PDO::PARAM_INT);
        // }
        if (isset($token_data->session) && !$reset) {
            $query = $this->c->queries['user']['get_by_session_token'];
            $query->bindValue(":session_token", base64_decode($token_data->session));
        } elseif (isset($token_data->reset) && $reset) {
            $query = $this->c->queries['user']['get_by_reset_token'];
            $query->bindValue(":reset_token", base64_decode($token_data->reset));
        } else {
            $response = $response->withJson([
                'error' => 'Invalid token'
            ], 403);
            return true;
        }

        $query->execute();

        $currentStatus = $this->errorResponseIfQueryBad(false, $response, $query);
        $currentStatus = $this->errorResponseIfQueryNoResults($currentStatus, $response, $query, 'Nonexistent token submitted');
        if ($currentStatus) {
            return true;
        }

        $user = $query->fetchAll()[0];
        return false;
    }
}
