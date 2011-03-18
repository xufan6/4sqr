<?php

class Foursquare {

        // 
        // GENERAL INFORMATION
        //
        // This is the version v0.1 of this class.
        // This class has been written by Romain Biard in february 2010
        // Romain Biard :
        //              - http://romainbiard.eu
        //              - http://romain.typepad.fr
        //              - @biskuit
        
        // Rrevision v0.2 by The Necenzurat
        // necenzurat@gmail.com
        // i had to do some changes for the class to work
        
        //
        // DESCRIPTION
        //
        // This class aims to help you to deal with the use of the Foursquare API.
        // There are 7 main types of methods
        //              - Geo Methods
        //              - Check-in Methods
        //              - User Methods
        //              - Venue Methods
        //              - Tips Methods
        //              - Other Methods
        //              - Test Method
        // This class will provide you the functions you need to call those methods.
        // Foursquare API returns either an error code, or in success cases, XML or JSON, depending on what you choose to use.
        //
        // This class uses the v1 of Foursquare's API.
        
        //
        // IMPROVEMENTS
        //
        // In the next version, this class should integrate functions to deal with errors.
        // A parser might also be included.
        
        //
        // HOW TO USE
        //
        // When you create a new object, put your credentials as parameters.
        // Example : foursquare = new Foursquare(login, pwd);
        //
        // Then you would like to define the type of format you wanna be returned.
        // Example : $foursquare->format = "json";
        //
        // Finally, you will just need to call a function.
        // Example : $json = $foursquare->historyCheckin("1", NULL);
        
        

        public $api = "api.foursquare.com/v1";
        public $format = NULL; // xml or json
        public $response = NULL;
        public $args = array();
        
        // Variables used by the methods
        // Be careful, some of them are used by different methods, don't forget to clear them or to unset your object before reuse them.
        
        
/********************************************************************************************************************************************************/
        
        // Constructeur de la classe file
        // Cette fonction est appelée au moment de l'initialisation de l'objet
        // et reçoit les arguments transmis à la classe lors de l'instanciation de l'objet.
        function __construct() {
                $this->api = "api.foursquare.com/v1";
                $this->response = NULL;
                $this->format = NULL;
                $this->args = array();
                
                // Passer les identifiants en paramètres
                // format : myClass = new foursquare(login, pwd);
                for($i=0;$i<func_num_args();$i++)
                        $this->args[$i] = func_get_arg($i);
        }
        
        
/********************************************************************************************************************************************************/

        // Call to the API
        
        function connect($method){
                $apicall = "http://".$this->api.$method;
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$apicall);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_USERAGENT, "necenzurat.com");
                //curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_USERPWD, $this->args[0].":".$this->args[1]);
                $xml = curl_exec ($ch);
                curl_close ($ch);
                
                return $xml;
        } 

        function connect2($method, $vars){
                $apicall = "http://".$this->api.$method;
                $vars = $method;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$apicall);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_USERAGENT, "necenzurat.com");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
                curl_setopt($ch, CURLOPT_USERPWD, $this->args[0].":".$this->args[1]);
                $xml = curl_exec ($ch);
                curl_close ($ch);
                
                return $xml;
        } 



        /********************************************************************************************************************************************************/

        // Check-in Methods


        // Checkins
        // Returns a list of recent checkins from friends.
        // If you pass in a geolat/geolong pair (optional, but recommended), we'll send you back a <distance> inside each <checkin> object that you can use to sort your results.
        
        // Parameters
        // geolat (optional, but recommended)
        // geolong (optional, but recommended)
        function checkins($geolat,$geolong) {
                $method = "/checkins".".".$this->format."?";

                if(isset($geolat) && $geolat != NULL)
                        $method .= "&geolat=".$geolat;
                        
                if(isset($geolong) && $geolong != NULL)
                        $method .= "&geolong=".$geolong;

                $this->response = $this->connect($method);
                return $this->response;
        }


        // Check-in
        // Allows you to check-in to a place.
        // A <mayor>  block will be returned if there's any mayor information for this place. It'll include a node <type>  which has the following values: new (the user has been appointed mayorship), nochange (the previous mayorship is still valid), stolen (the user stole mayorship from the previous mayor).
        // A <specials>  block will be returned if there are any specials associated with this check-in. It'll include subnodes <special> which may have various types. The types can be one of: mayor, count, frequency, or other. If the special is at a nearby venue (instead of at the currently checked-into venue), you'll see a <venue> node inside <special>  that will highlight the nearby venue.
        
        // Parameters
        // vid: (optional, not necessary if you are 'shouting' or have a venue name). ID of the venue where you want to check-in
        // venue: (optional, not necessary if you are 'shouting' or have a vid) if you don't have a venue ID or would rather prefer a 'venueless' checkin, pass the venue name as a string using this parameter. it will become an 'orphan' (no address or venueid but with geolat, geolong)
        // shout: (optional) a message about your check-in. the maximum length of this field is 140 characters
        // private: (optional). "1" means "don't show your friends". "0" means "show everyone"
        // twitter: (optional, defaults to the user's setting). "1" means "send to Twitter". "0" means "don't send to Twitter"
        // facebook: (optional, defaults to the user's setting). "1" means "send to Facebook". "0" means "don't send to Facebook"
        // geolat: (optional, but recommended)
        // geolong: (optional, but recommended)
        function checkin($vid,$venue,$shout,$private,$twitter,$facebook,$geolat,$geolong) {
                $method = "/checkin".".".$this->format."?";

                if(isset($vid) && $vid != NULL && ($vid > 0))
                        $method .= "&vid=".$vid;
                
                if(isset($venue) && $venue != NULL && ($venue > 0))
                        $method .= "&venue=".urlencode($venue);
                        
                if(isset($shout) && $shout != NULL && strlen($shout) <= 140)
                        $method .= "&shout=".urlencode($shout);
                        
                if(isset($private) && $private != NULL && ($private ==0 || $private == 1))
                        $method .= "&private=".$private;
                        
                if(isset($twitter) && $twitter != NULL && ($twitter ==0 || $twitter == 1))
                        $method .= "&twitter=".$twitter;
                        
                if(isset($facebook) && $facebook != NULL && ($facebook ==0 || $facebook == 1))
                        $method .= "&facebook=".$facebook;
                
                if(isset($geolat) && $geolat != NULL)
                        $method .= "&geolat=".$geolat;
                        
                if(isset($geolong) && $geolong != NULL)
                        $method .= "&geolong=".$geolong;

                $this->response = $this->connect2($method, $method);
                return $this->response;
        }
        
        
        // History
        // Returns a history of checkins for the authenticated user (across all cities).
        
        // Parameter
        // limit is the limit of results (optional, default: 20). number of checkins to return
        // sinceid, id to start returning results from (optional, if omitted returns most recent results)
        function historyCheckin($limit,$sinceid) {
                $method = "/history".".".$this->format."?";
                
                if(isset($limit) && $limit != NULL && ($limit > 0))
                        $method .= "&l=".$limit;

                if(isset($sinceid) && $sinceid != NULL && ($sinceid > 0))
                        $method .= "&sinceid=".$sinceid;
                        
                $this->response = $this->connect($method);
                return $this->response;
        }


        /********************************************************************************************************************************************************/

        // User Methods
        
        
        // User detail
        // Returns profile information (badges, etc) for a given user. If the user has recent check-in data (ie, if the user is self or is a friend of the authenticating user), this data will be returned as well in a <checkin> block.
        // If the user requested is 'self' (ie, the authenticating user), a <settings> block with defaults will be returned. This settings block includes attributes like <sendtotwitter>, <sendtofacebook> and the user's RSS/KML private feeds key. sendtotwitter will indicate whether the default action is to tweet check-in information to Twitter (the possible values are true and false). sendtofacebook will indicate whether the default action is to send check-in information to the user's Facebook news feed. pings will indicate whether the user will receive check-in notification pings from client apps (iPhone, Android, Blackberry, ...). The possible values for pings are: on (send pings), off (don't send pings) and goodnight  (don't send pings again until 7AM in the user's current timezone).
        // Lastly, if the user is a friend of the authenticating user, you'll have access to the requested user's phone, email, Twitter and Facebook ID. In addition to this, you'll also see a setting get_pings. get_pings will indicate whether the authenticating user is setup to receive check-in pings from the friend (push notifications, etc). The possible values for get_pings are true and false.
        
        // Parameters
        // uid: userid for the user whose information you want to retrieve. If you do not specify a 'uid', the authenticated user's profile data will be returned.
        // mayor: (optional, default: false) set to true ("1") to also show venues for which this user is a mayor. by default, this will show mayorships worldwide.
        // badges: (optional, default: false) set to true ("1") to also show badges for this user. by default, this will only show badges from the authenticated user's current city.
        function userDetail($uid,$mayor,$badges) {
                $method = "/user".".".$this->format."?";
                
                if(isset($uid) && $uid != NULL && ($uid > 0))
                        $method .= "&uid=".$uid;
                        
                if(isset($mayor) && $mayor != NULL && ($mayor ==0 || $mayor == 1))
                        $method .= "&mayor=".$mayor;
                        
                if(isset($badges) && $badges != NULL && ($badges ==0 || $badges == 1))
                        $method .= "&badges=".$badges;
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        // Friends
        // Returns a list of friends. If you do not specify uid, the authenticating user's list of friends will be returned. If the friend has allowed it, you'll also see links to their Twitter and Facebook accounts.
        
        // Parameters
        // uid: user id of the person for whom you want to pull a friend graph (optional)
        function friends($uid) {
                $method = "/friends".".".$this->format."?";
                
                if(isset($uid) && $uid != NULL && ($uid > 0))
                        $method .= "&uid=".$uid;
                
                $this->response = $this->connect($method);
                return $this->response;
        }


        /********************************************************************************************************************************************************/

        // Venue Methods
        
        
        // Nearby and Search
        // Returns a list of friends. If you do not specify uid, the authenticating user's list of friends will be returned. If the friend has allowed it, you'll also see links to their Twitter and Facebook accounts.
        // Note that most of the fields returned inside <venue> can be optional. The user may create a venue that has no address, city or state (the venue is created instead at the geolat/geolong specified). Your client should handle these conditions safely.
        
        // Parameters
        // geolat: latitude (required)
        // geolong: longitude (required)
        // limit: limit of results (optional, default 10, maximum 50)
        // q: keyword search (optional)
        function nearbyAndSearch($geolat,$geolong,$limit,$q) {
                $method = "/venues".".".$this->format."?geolat=".$geolat."&geolong=".$geolong;
                
                
                
                if(isset($limit) && $limit != NULL && ($limit > 0) && ($limit <= 50))
                        $method .= "&l=".$limit;
                        
                if(isset($q) && $q != NULL && ($q > 0))
                        $method .= "&q=".urlencode($q);
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        // Venue details
        // Returns venue data, including mayorship, tips/to-dos and tags.
        // A <specials>  block will be returned if there are any specials associated with this venue. It'll include subnodes <special> which may have various types. The types can be one of: mayor, count, frequency, or other. If the special is at a nearby venue (instead of at the currently visible venue), you'll see a <venue> node inside <special>  that will highlight the nearby venue.
        // If you authenticate, you'll get back social meta data:
        //              + stats - shows whether you and your friends have ever checked in here (<beenhere>)
        //              + checkins - show the people currently checked into this location (ie, last three hours). you'll see <shout> and full <lastname> if they are friends with the authenticating user
        
        // Parameters
        // vid: the ID for the venue for which you want information
        function venueDetails($vid) {
                $method = "/venue".".".$this->format."?vid=".$vid;
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        // Add venue
        // Allows you to add a new venue.
        // If you find this method returns an <error>, give the user the option to edit her inputs. In addition to this, give users the ability to say "never mind, check-in here anyway" and perform a manual ("venueless") checkin by specifying just the venue name to /v1/checkin. You'll rarely run into this case, but there's a chance you'll see this case if the user wants to force a duplicate venue.
        // All fields are optional, but you must specify either a valid address or a geolat/geolong pair. It's recommended that you pass a geolat/geolong pair in every case.
        
        // Parameters
        // name: the name of the venue
        // address: (optional) the address of the venue (e.g., "202 1st Avenue")
        // crossstreet: (optional) the cross streets (e.g., "btw Grand & Broome")
        // city: (optional) the city name where this venue is
        // state: (optional) the state where the city is
        // zip: (optional) the ZIP code for the venue
        // phone: (optional) the phone number for the venue
        // geolat: (optional, but recommended)
        // geolong: (optional, but recommended)
        function addVenue($name,$address,$crossstreet,$city,$state,$zip,$phone,$geolat,$geolong) {
                $method = "/addvenue".".".$this->format."?";
                
                if(isset($name) && $name != NULL)
                        $method .= "&name=".urlencode($name);
                        
                if(isset($address) && $address != NULL)
                        $method .= "&address=".urlencode($address);
                        
                if(isset($crossstreet) && $crossstreet != NULL)
                        $method .= "&crossstreet=".urlencode($crossstreet);
                        
                if(isset($city) && $city != NULL)
                        $method .= "&city=".urlencode($city);
                        
                if(isset($state) && $state != NULL)
                        $method .= "&state=".urlencode($state);
                        
                if(isset($zip) && $zip != NULL)
                        $method .= "&zip=".$zip;
                        
                if(isset($phone) && $phone != NULL)
                        $method .= "&phone=".$phone;
                
                if(isset($geolat) && $geolat != NULL)
                        $method .= "&geolat=".$geolat;
                        
                if(isset($geolong) && $geolong != NULL)
                        $method .= "&geolong=".$geolong;
                
                $this->response = $this->connect2($method, $method);
                return $this->response;
        }
        
        
        // Propose venue edit
        // Allows you to flag/propose a change to a venue.
        // 
        
        // Parameters
        // vid: (required) the venue for which you want to propose an edit
        // name: (required) the name of the venue
        // address: (required) the address of the venue (e.g., "202 1st Avenue")
        // crossstreet: the cross streets (e.g., "btw Grand & Broome")
        // city: (required) the city name where this venue is
        // state: (required) the state where the city is
        // zip: (optional) the ZIP code for the venue
        // phone: (optional) the phone number for the venue
        // geolat: (required)
        // geolong: (required)
        function proposeEdit($vid,$name,$address,$crossstreet,$city,$state,$zip,$phone,$geolat,$geolong) {
                $method = "/venue/proposeedit".".".$this->format."?vid=".$vid."&name=".urlencode($name)."&address=".urlencode($address)."&city=".urlencode($city)."&state=".urlencode($state)."&geolat=".$geolat."&geolong=".$geolong;
                
                if(isset($crossstreet) && $crossstreet != NULL)
                        $method .= "&crossstreet=".urlencode($crossstreet);
                
                if(isset($zip) && $zip != NULL)
                        $method .= "&zip=".$zip;
                        
                if(isset($phone) && $phone != NULL)
                        $method .= "&phone=".$phone;
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        // Flag venue as closed
        // Allows you to mark a tip as a to-do item.
        
        // Parameters
        // vid: the venue that you want marked closed (required)
        function flagAsClosed($vid) {
                $method = "/venue/flagclosed".".".$this->format."?vid=".$vid;
                
                $this->response = $this->connect($method);
                return $this->response;
        }


        /********************************************************************************************************************************************************/

        // Tip Methods
        
        
        // Nearby
        // Returns a list of tips near the area specified. (The distance returned is in meters).
        
        // Parameters
        // geolat: latitude (required)
        // geolong: longitude (required)
        // limit: limit of results (optional, default30)
        function nearby($geloat,$geolong,$l) {
                $method = "/tips".".".$this->format."?geolat=".$geolat."&geolong=".$geolong;
                
                if(isset($limit) && $limit != NULL && ($limit > 0))
                        $method .= "&l=".$limit;
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        // Add tip / to-do
        // Allows you to add a new tip or to-do at a venue.
        
        // Parameters
        // vid: the venue where you want to add this tip (required)
        // tex: the text of the tip or to-do item (required)
        // type: specify one of 'tip' or 'todo' (optional, default: tip)
        // geolat: latitude (optional, but recommended)
        // geolong: longitude (optional, but recommended)
        function addTip($vid,$text,$type,$geloat,$geolong) {
                $method = "/addtip".".".$this->format."?vid=".$vid."&text=".urlencode($text);
                
                if(isset($type) && $type != NULL && strcmp($type,"tip")==0 && strcmp($type,"todo")==0)
                        $method .= "&type=".$type;
                
                if(isset($geolat) && $geolat != NULL)
                        $method .= "&geolat=".$geolat;
                        
                if(isset($geolong) && $geolong != NULL)
                        $method .= "&geolong=".$geolong;
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        // Mark tip as to-do
        // Allows you to mark a tip as a to-do item.
        
        // Parameters
        // tid: the tip that you want to mark to-do (required)
        function markTip($tid) {
                $method = "/tip/marktodo".".".$this->format."?tid=".$tid;
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        // Mark tip as done
        // Allows you to mark a tip as done.
        
        // Parameters
        // tid: the tip that you want to mark to-do (required)
        function markTipDone($tid) {
                $method = "/tip/markdone".".".$this->format."?tid=".$tid;
                
                $this->response = $this->connect($method);
                return $this->response;
        }


        /********************************************************************************************************************************************************/

        // Friend Methods
        
        
        // Pending friend requests
        // Shows you a list of users with whom you have a pending friend request (ie, they've requested to add you as a friend, but you have not approved).
        
        // Parameters : none
        function friendRequests() {
                $method = "/friend/requests".".".$this->format;
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        // Approve friend request
        // Allows you to mark a tip as done.
        
        // Parameters
        // uid: the user ID of the user who you want to approve
        function approveRequest($uid) {
                $method = "/friend/approve".".".$this->format."?uid=".$uid;
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        // Deny friend request
        // Denies a pending friend request from another user. On success, returns the <user>  object.
        
        // Parameters
        // uid: the user ID of the user who you want to deny
        function denyRequest($uid) {
                $method = "/friend/deny".".".$this->format."?uid=".$uid;
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        // Send friend request
        // Sends a friend request to another user. On success, returns the <user> object.
        
        // Parameters
        // uid: the user ID of the user to whom you want to send a friend request
        function sendRequest($uid) {
                $method = "/friend/sendrequest".".".$this->format."?uid=".$uid;
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        // Find friends by name
        // When passed a free-form text string, returns a list of matching <user> objects. The method only returns matches of people with whom you are not already friends. 
        
        // Parameters
        // q: the string you want to use to search firstnames and lastnames
        function findFriendsName($q) {
                $method = "/findfriends/byname".".".$this->format."?q=".urlencode($q);
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        // Find friends by phone
        // When passed phone number(s), returns a list of matching <user> objects. The method only returns matches of people with whom you are not already friends. You can pass a single number as a parameter, or you can pass multiple numbers separated by commas.
        
        // Parameters
        // q: the string you want to use to search for phone numbers
        function findFriendsPhone($q) {
                $method = "/findfriends/byphone".".".$this->format."?q=".urlencode($q);
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        // Find friends by using a Twitter name
        // When passed a Twitter name (user A), returns a list of matching <user> objects that correspond to user A's friends on Twitter. The method only returns matches of people with whom you are not already friends.
        // If you don't pass in a Twitter name, it will attempt to use the Twitter name associated with the authenticating user.
        
        // Parameters
        // q: (optional) the Twitter name you want to use to search
        function findFriendsTwitter($q) {
                $method = "/findfriends/bytwitter".".".$this->format;
                
                if(isset($q) && $q != NULL)
                        $method .= "?q=".$q;
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        
        /********************************************************************************************************************************************************/

        // Setting Methods
        
        
        // Set Pings
        // Allows you to change notification options for yourself (self) globally as well as for each individual friend (identified by their uid).
        // For example: To set pings on for a user identified by UID 33: "33=on". To set pings to 'goodnight' for yourself: "self=goodnight".
        
        // Parameters
        // self: the ping status for yourself (globally). possible values are on, off and goodnight.
        // [uid]: set the ping status for a friend. possible values are on and off.
        function setPings($who,$value) {
                $method = "/settings/setpings".".".$this->format;
                
                if(strcmp($who,"self")==0)
                        $method .= "?self=".$value;
                else
                        $method .= "?uid=".$value;
                
                $this->response = $this->connect($method);
                return $this->response;
        }
        
        
        
        /********************************************************************************************************************************************************/

        // Other Methods
        
        
        // Test
        // Returns the string "ok".
        
        // Parameters : none
        function test() {
                $method = "/test".".".$this->format;
                
                $this->response = $this->connect($method);
                return $this->response;
        }

        
        
/********************************************************************************************************************************************************/
        
        // Destruction of the variables used.
        // Called by the function unset($nomdelobjet).
        function __destruct() {
                $this->api = "api.foursquare.com/v1";
                $this->response = NULL;
                $this->format = NULL;
                $this->args = array();
        }

}

