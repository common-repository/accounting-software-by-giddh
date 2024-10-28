<?php
class GiddhApiRequest {

    public static function jsonPost($url, $parameters, $authKey = false) {
        try {
            if($authKey) {
                $headers = array('Content-Type' => 'application/json', 'auth-key' => $authKey, 'Content-Length' => strlen(json_encode($parameters)));
            } else {
                $headers = array('Content-Type' => 'application/json');
            }
            
            $response = wp_remote_post($url, array(
                'method' => 'POST',
                'body' => wp_json_encode($parameters),
                'headers' => $headers
            ));

            $resultobject = json_decode($response['body']);
            $result = json_decode(json_encode($resultobject), true);

            return $result;
        } catch(Exception $e) {
            return array();
        }    
    }

    public static function get($url, $authKey) {
        try {
            $response = wp_remote_get($url, array(
                'headers' => array('auth-key' => $authKey)
            ));

            $resultobject = json_decode($response['body']);
            $result = json_decode(json_encode($resultobject), true);

            return $result;
        } catch(Exception $e) {
            return array();
        }
    }

    public static function jsonPut($url, $parameters, $authKey = false) {
        try {
            if($authKey) {
                $headers = array('Content-Type' => 'application/json', 'auth-key' => $authKey, 'Content-Length' => strlen(json_encode($parameters)));
            } else {
                $headers = array('Content-Type' => 'application/json');
            }
            
            $response = wp_remote_post($url, array(
                'method' => 'PUT',
                'body' => wp_json_encode($parameters),
                'headers' => $headers
            ));

            $resultobject = json_decode($response['body']);
            $result = json_decode(json_encode($resultobject), true);

            return $result;
        } catch(Exception $e) {
            return array();
        }    
    }
}
?>