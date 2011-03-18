<?php
        require_once('class.restapi.php');
        
        class Foursquare extends RestApi
        {
                protected $cache_ext = 'foursquare';
                protected $endpoint = 'https://api.foursquare.com/v2/';
                protected $consumer_key;
                protected $consumer_secret;
                protected $token;
                protected $format = 'json';
                
                function __construct($consumer_key = "", $consumer_secret = "")
                {
                        $this->consumer_key = $consumer_key;
                        $this->consumer_secret = $consumer_secret;
                }
                
                function login($token)
                {
                        $this->token = $token;
                }
                
                function getAuthorizeUrl($callback)
                {
                        $callback = urlencode($callback);
                        return "https://foursquare.com/oauth2/authenticate?client_id={$this->consumer_key}&response_type=code&redirect_uri={$callback}";
                }
                
                function getAccessToken($code, $callback)
                {
                        $params['client_id'] = $this->consumer_key;
                        $params['client_secret'] = $this->consumer_secret;
                        $params['grant_type'] = 'authorization_code';
                        $params['redirect_uri'] = $callback;
                        $params['code'] = $code;
                        return $this->request("https://foursquare.com/oauth2/access_token", array('cache_life'=>0, 'get'=>$params));
                }
                
                function request($url, $extra = array(), $force_post = false)
                {
                        if ($this->token)
                        {
                                if (is_array($extra['post']))
                                        $extra['post']['oauth_token'] = $this->token;
                                elseif ($extra['post'])
                                        $extra['post'].="&oauth_token={$this->token}";
                                elseif ($force_post)
                                        $extra['post']['oauth_token'] = $this->token;
                                else
                                        $extra['get']['oauth_token'] = $this->token;
                        }
                        return parent::request($url, $extra, $force_post);
                }
                
                function requestObjectGroups($name, $url, $extra = array(), $force_post = false)
                {
                        $groups = $this->requestObject('groups', $url, $extra, $force_post);
                        if (is_array($groups))
                        {
                                $return = array();
                                foreach ($groups as $group)
                                {
                                        if (isset($group->type) && isset($group->$name) && is_array($group->$name))
                                                $return[$group->type] = $group->$name;
                                }
                        }
                        else
                                $return = false;
                        return $return;
                }
                
                function requestObjectItems($name, $url, $extra = array(), $force_post = false)
                {
                        $obj = $this->requestObject($name, $url, $extra, $force_post);
                        if (is_object($obj) && isset($obj->items))
                                return $obj->items;
                        else
                                return false;
                }
                function requestObject($name, $url, $extra = array(), $force_post = false)
                {
                        $obj = $this->request($url, $extra, $force_post);
                        if (is_object($obj) && isset($obj->response) && isset($obj->response->$name))
                                return $obj->response->$name;
                        else
                                return false;
                }
                
                // *** Venue methods ***
                function venues($lat = null, $long = null, $q = null, $limit = null)
                {
                        $url = "{$this->endpoint}venues/search";
                        $get = array();
                        
                        if (!$this->token)
                        {
                                $get['client_id'] = $this->consumer_key;
                                $get['client_secret'] = $this->consumer_secret;
                        }
                        
                        if (!is_null($lat) && !is_null($long))
                                $get['ll'] = "$lat,$long";
                        if (!is_null($q))
                                $get['q'] = $q;
                        if (!is_null($limit))
                                $get['l'] = $limit;
                        return $this->requestObjectGroups('items', $url, array('get'=>$get));
                }
                
                // *** User methods ***
                function user_checkins($limit = null)
                {
                        $url = "{$this->endpoint}users/self/checkins";
                        $get = array();
                        if (!is_null($limit))
                                $get['limit'] = $limit;
                        return $this->requestObjectItems('checkins', $url, array('get'=>$get));
                }
                
                function user_venuehistory()
                {
                        $url = "{$this->endpoint}users/self/venuehistory";
                        return $this->requestObjectItems('venues', $url);
                }
                
                function user($user_id)
                {
                        $url = "{$this->endpoint}users/$user_id";
                        return $this->requestObject('user', $url);
                }
                
        }
