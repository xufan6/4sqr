<?php
        require_once('class.restapi.php');
        
        class Foursquare extends RestApi
        {
                protected $cache_ext = 'foursquare';
                protected $endpoint = 'http://api.foursquare.com/v1/';
                
                function __construct($username = false, $password = false)
                {
                        if ($username && $password)
                                $this->login($username, $password);
                        parent::__construct();
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
                
                function requestObject($name, $url, $extra = array(), $force_post = false)
                {
                        $obj = $this->request($url, $extra, $force_post);
                        if (is_object($obj) && isset($obj->$name))
                                return $obj->$name;
                        else
                                return false;
                }
                
                // *** Geo methods ***
                function cities()
                {
                        $url = "{$this->endpoint}cities.{$this->format}";
                        return $this->requestObject('cities', $url);
                }
                
                function checkCity($lat,$long)
                {
                        $url = "{$this->endpoint}checkcity.{$this->format}";
                        $get = array('geolat'=>$lat, 'geolong'=>$long);
                        return $this->requestObject('city', $url, array('get'=>$get));
                }
                
                function switchCity($cityid)
                {
                        $url = "{$this->endpoint}switchcity.{$this->format}";
                        return $this->requestObject('data', $url, array('post'=>array('cityid'=>$cityid)));
                }
                
                // *** Check in methods ***
                function checkins($cityid = null)
                {
                        $url = "{$this->endpoint}checkins.{$this->format}";
                        $get = array();
                        if (!is_null($cityid))
                                $get['cityid'] = $cityid;
                        return $this->requestObject('checkins', $url, array('get'=>$get));
                }
                
                /*
                        Params for checkin():
                        vid - (optional, not necessary if you are 'shouting' or have a venue name). ID of the venue where you want to check-in.
                        venue - (optional, not necessary if you are 'shouting' or have a vid) if you don't have a venue ID, pass the venue name as a string using this parameter. foursquare will attempt to match it on the server-side
                        shout - (optional) a message about your check-in. the maximum length of this field is 140 characters
                        private - (optional). "1" means "don't show your friends". "0" means "show everyone"
                        twitter - (optional, default to the user's setting). "1" means "send to twitter". "0" means "don't send to twitter"
                        geolat - (optional, but recommended)
                        geolong - (optional, but recommended)
                */
                function checkin($params)
                {
                        $url = "{$this->endpoint}checkin.{$this->format}";
                        return $this->requestObject('checkin', $url, array('post'=>$params));
                }
                
                function history($limit = null)
                {
                        $url = "{$this->endpoint}history.{$this->format}";
                        $get = array();
                        if (!is_null($limit))
                                $get['l'] = $limit;
                        return $this->requestObject('checkins', $url, array('get'=>$get));
                }
                
                // *** User methods ***
                function user($userid = null, $badges = null, $mayor = null)
                {
                        $url = "{$this->endpoint}user.{$this->format}";
                        $get = array();
                        if (!is_null($userid))
                                $get['uid'] = $userid;
                        if (!is_null($badges))
                                $get['badges'] = $badges?1:0;
                        if (!is_null($mayor))
                                $get['mayor'] = $mayor?1:0;
                        return $this->requestObject('user', $url, array('get'=>$get));
                }
                
                function friends($uid = null)
                {
                        $url = "{$this->endpoint}friends.{$this->format}";
                        $get = array();
                        if (!is_null($uid))
                                $get['uid'] = $uid;
                        return $this->requestObject('friends', $url, array('get'=>$get));
                }
                
                // *** Venue methods ***
                function venues($lat, $long, $q = null, $limit = null)
                {
                        $url = "{$this->endpoint}venues.{$this->format}";
                        $get = array('geolat'=>$lat, 'geolong'=>$long);
                        if (!is_null($q))
                                $get['q'] = $q;
                        if (!is_null($limit))
                                $get['l'] = $limit;
                        return $this->requestObjectGroups('venues', $url, array('get'=>$get));
                }
                
                function venue($vid)
                {
                        $url = "{$this->endpoint}venue.{$this->format}";
                        return $this->requestObject('venue', $url, array('get'=>array('vid'=>$vid)));
                }
                
                /*
                        Params for addVenue():
                        name - the name of the venue
                        address - the address of the venue (e.g., "202 1st Avenue")
                        crossstreet - the cross streets (e.g., "btw Grand & Broome")
                        city - the city name where this venue is
                        state - the state where the city is
                        zip - (optional) the ZIP code for the venue
                        cityid - (required) the foursquare cityid where the venue is
                        phone - (optional) the phone number for the venue
                */
                function addVenue($params)
                {
                        $url = "{$this->endpoint}addvenue.{$this->format}";
                        return $this->requestObject('venue', $url, array('post'=>$params));
                }
                
                // *** Tip methods ***
                function tips($lat, $long, $limit = null)
                {
                        $url = "{$this->endpoint}tips.{$this->format}";
                        $get = array('geolat'=>$lat, 'geolong'=>$long);
                        if (!is_null($limit))
                                $get['l'] = $limit;
                        return $this->requestObjectGroups('tips', $url, array('get'=>$get));
                }
                
                function addTip($vid, $text, $type = null)
                {
                        $url = "{$this->endpoint}addtip.{$this->format}";
                        $post = array('vid'=>$vid, 'text'=>$text);
                        if (!is_null($type))
                                $post['type'] = $type;
                        return $this->requestObject('tip', $url, array('post'=>$post));
                }
        }
