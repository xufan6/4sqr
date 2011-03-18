<?php
        /*
                PHP REST API
                Jason Tan
                http://code.google.com/p/php-rest-api/
        */
        class RestApi
        {
                protected $username;
                protected $password;
                protected $format = "json";
                protected $cache_life = 3600;
                protected $cache_dir = "cache";
                protected $cache_ext;
                public $debug = false;

                function __construct()
                {
                }
                
                function login($username, $password)
                {
                        $this->username = $username;
                        $this->password = $password;
                }
                
                function logout()
                {
                        $this->username = null;
                        $this->password = null;
                }
                
                function setFormat($format)
                {
                        $this->format = $format;
                }
                
                function setCache($life, $dir, $ext)
                {
                        $this->cache_life = $life;
                        $this->cache_dir = $dir;
                        $this->cache_ext = $ext;
                }
                
                function setCacheLife($life)
                {
                        $this->cache_life = $life;
                }
                
                function getCacheFile($url, $post)
                {
                        $cache_file = $this->cache_dir . '/' .  md5($url.'|'.$post.'|'.$this->username.'|'.$this->password);
                        if ($this->cache_ext)
                                $cache_file .= ".{$this->cache_ext}";
                        return $cache_file;
                }
                
                function setCurlOpts($ch)
                {
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                }
                
                function request($url, $extra = array(), $force_post = false)
                {
                        if (isset($extra['cache_life']))
                                $cache_life = $extra['cache_life'];
                        else
                                $cache_life = $this->cache_life;
                                
                        if (isset($extra['get']) && is_array($extra['get']) && count($extra['get'] > 0))
                        {
                                $url .= '?';
                                $url .= http_build_query($extra['get']);
                        }
                        
                        if (isset($extra['post']))
                        {
                                if (is_array($extra['post']) && count($extra['post'] > 0))
                                {
                                        $post = "";
                                        $first = true;
                                        foreach ($extra['post'] as $param=>$value)
                                        {
                                                if (!$first)
                                                        $post .= '&';
                                                else
                                                        $first = false;
                                                //$post .= urlencode($param) . '=' . urlencode($value);
                                                $post .= $param . '=' . $value;
                                        }
                                }
                                elseif (is_string($extra['post']))
                                {
                                        $post = $extra['post'];
                                }
                        }
                        else
                                $post = false;
                        
                        $this->cache_dir = rtrim($this->cache_dir, '/');
                        if ($post===false && $force_post===false && $cache_life && $this->cache_dir && is_dir($this->cache_dir) && is_writable($this->cache_dir))
                                $use_cache = true;
                        else
                                $use_cache = false;
                        
                        if ($use_cache)
                        {
                                $cache_file = $this->getCacheFile($url, $post);
                                if ($this->debug)
                                        echo "CHECKING CACHE: $cache_file\n";
                                if (file_exists($cache_file) && ($cache_life < 0 || filemtime($cache_file) > time()-($this->cache_life)))
                                {       
                                        if ($this->debug)
                                                echo "USING CACHED DATA: $cache_file\n";
                                        return $this->objectify(file_get_contents($cache_file));
                                }
                        }
                        
                        if ($this->debug)
                                echo "REQUEST: $url\n";

                        $ch = curl_init($url);
                        if($post !== false || $force_post)
                        {
                                if ($this->debug)
                                        echo "POST: $post\n";
                                curl_setopt($ch, CURLOPT_POST, true);
                                if ($post)
                                        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                }

                        if(!is_null($this->username) && !is_null($this->password))
                        {
                                if ($this->debug)
                                        echo "AUTH: {$this->username}:{$this->password}\n";
                                curl_setopt($ch, CURLOPT_USERPWD, $this->username.':'.$this->password);
                        }
                        
                        if (isset($extra['headers']) && is_array($extra['headers']) && count($extra['headers'] > 0))
                        {
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $extra['headers']);
                        }
                        
                        $this->setCurlOpts($ch);
                        
                $response = curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
                        
                        if ($this->debug)
                        {
                                echo "\nINFO:\n";
                                print_r($info);
                                echo "\nRESPONSE:\n";
                                echo htmlspecialchars($response);
                                echo "\n";
                        }

                        $object = $this->verify($info, $response);
                        
                        if ($object !== false && !is_null($object))
                        {
                                if ($use_cache)
                                {
                                        if ($this->debug)
                                                echo "CACHE: writing to $cache_file\n";
                                        file_put_contents($cache_file, $response);
                                }
                                return $object;
                        }

                        if ($use_cache && file_exists($cache_file))
                                return $this->objectify(file_get_contents($cache_file));
                        else
                                return false;
                }
                
                function verify($info, $response)
                {
                        if (!preg_match('/^2[0-9]{2}$/', $info['http_code']))
                                return false;
                        
                        if ($response === false)
                                return false;

                        return $this->objectify($response);
                }

                function objectify($response)
                {
                        switch ($this->format)
                        {
                                case 'json':
                                case 'js':
                                        return json_decode($response);
                                        break;
                                case 'xml':
                                case 'atom':
                                case 'rss':
                                        return simplexml_load_string($response);
                                        break;
                                case 'php':
                                case 'php_serial':
                                        return unserialize($response);
                                default:
                                        return $response;
                        }
                }
        }
        
        require_once('class.oauth.php');

        class OAuthRestApi extends RestApi
        {
                protected $oa_method;
                protected $consumer;
                protected $request_token;
                protected $access_token;
                
                function __construct($consumer_key, $consumer_secret)
                {
                    $this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);                       
                    $this->oa_method = new OAuthSignatureMethod_HMAC_SHA1();
                        parent::__construct();
                }
                
                function login($oauth_token, $oauth_token_secret)
                {
                        $this->access_token = new OAuthConsumer($oauth_token, $oauth_token_secret);
                }
                
                static function parseToken($string)
                {
                        $token = array();
                        parse_str($string, $token);
                        if (isset($token['oauth_token']) && isset($token['oauth_token_secret']))
                                return new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
                        else
                                return false;
                }
                
                function getAuthorizeUrl($request_url, $authorize_url, $callback = false)
                {
                        $req = OAuthRequest::from_consumer_and_token($this->consumer, null, 'GET', $request_url);
                    $req->sign_request($this->oa_method, $this->consumer, null);
                
                        $format = $this->format;
                        $this->format = "text";
                        $result = parent::request($req->to_url(), array('cache_life'=>0));
                        $this->format = $format;
                        
                        if (($token = self::parseToken($result))===false)
                                return false;
                        
                        $this->request_token = $token;
                        $_SESSION['request_token'] = $token;
                        
                        $authorize_url .= "?oauth_token={$this->request_token->key}";
                        if ($callback)
                                $authorize_url .= "&oauth_callback=" . urlencode($callback);
                        return $authorize_url;
                }
                
                function getAccessToken($access_url)
                {
                        if (!is_object($this->request_token))
                        {
                                if (is_object($_SESSION['request_token']))
                                        $this->request_token = $_SESSION['request_token'];
                                else
                                        return false;
                        }
                        $req = OAuthRequest::from_consumer_and_token($this->consumer, $this->request_token, 'GET', $access_url);
                        $req->sign_request($this->oa_method, $this->consumer, $this->request_token);
                
                        $format = $this->format;
                        $this->format = "text";
                        $result = parent::request($req->to_url(), array('cache_life'=>0));
                        $this->format = $format;
                        
                        if (($token = self::parseToken($result))===false)
                                return false;
                        return $this->access_token = $token;
                }
                
                function getCacheFile($url)
                {
                        $url = preg_replace('/[\?|&]oauth_version.*$/','',$url);
                        $cache_file = $this->cache_dir . '/' .  md5($url.'|'.$this->access_token->key);
                        if ($this->cache_ext)
                                $cache_file .= ".{$this->cache_ext}";
                        return $cache_file;
                }

                function request($url, $extra = array(), $force_post = false)
                {
                        $oauth = array(
                                'oauth_version' => OAuthRequest::$version,
                                'oauth_nonce' => OAuthRequest::generate_nonce(),
                                'oauth_timestamp' => OAuthRequest::generate_timestamp(),
                                'oauth_consumer_key' => $this->consumer->key,
                                'oauth_token' => $this->access_token->key,
                                'oauth_signature_method'=>$this->oa_method->get_name()
                        );
                        
                        if (isset($extra['post']))
                                $params = $extra['post'];
                        elseif (isset($extra['get']))
                                $params = $extra['get'];
                        else
                                $params = array();
                        
                        if (isset($extra['post']) || $force_post)
                                $method = 'POST';
                        else
                                $method = 'GET';
                        
                        $params = array_merge($params, $oauth);
                        $request = new OAuthRequest($method, $url, $params);
                    $params['oauth_signature'] = $request->build_signature($this->oa_method, $this->consumer, $this->access_token);

                        $extra[strtolower($method)] = $params;
                    
                        return parent::request($url, $extra, $force_post);
                }
        }

