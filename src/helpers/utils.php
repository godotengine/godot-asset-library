<?php

class Utils
{
  var $c;
  public function __construct($c)
  {
    $this->c = $c;
  }

  public function error_reponse_if_missing_or_not_string($currentStatus, &$response, $object, $property)
  {
    if($currentStatus) {
      return true;
    }
    if((!isset($object[$property]) || !is_string($object[$property]))) {
      $response = $response->withJson([
        'error' => $property . ' is required, and must be a string'
      ], 400);
      return true;
    }
    return false;
  }

  public function error_reponse_if_query_bad($currentStatus, &$response, $query)
  {
    if($currentStatus) {
      return true;
    }
    if($query->errorCode() != '00000') {
      $this->c->logger->error($query->errorCode(), $query->errorInfo());
      $response = $response->withJson([
        'error' => 'An error occured while executing DB queries'
      ], 500);
      return true;
    }
    return false;
  }

  public function error_reponse_if_query_no_results($currentStatus, &$response, $query)
  {
    if($currentStatus) {
      return true;
    }
    if($query->rowCount() == 0) {
      $response = $response->withJson([
        'error' => 'DB returned no results'
      ], 400);
      return true;
    }
    return false;
  }

  public function get_user_id_from_token_data($token_data)
  {
    if(!$token_data) {
      return false;
    }
    if(isset($token_data->user_id)) {
      return (int) $token_data->user_id;
    }
    if(isset($token_data->session)) {
      $sesion_query = $this->c->queries['user']['get_by_session_token'];
      $sesion_query->bindValue(":session_token", $token_data->session);
      $sesion_query->execute();
      if($sesion_query->errorCode() != '00000') {
        return false;
      }
      $results = $sesion_query->fetchAll();
      if(count($results) == 0) {
        return false;
      }
      return (int) $results[0]["id"];
    }
    return false;
  }
}

