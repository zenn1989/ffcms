<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;
use DateTime;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class system extends singleton {

    protected static $instance = null;

    const MAX_INTEGER_32 = 2147483647;

    /**
     * @return system
     */
    public static function getInstance() {
        if(is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    /**
     * Get data from global $_POST with $key. Like $_POST[$key]
     * @param string|null $key
     * @return string|null
     */
    public function post($key = null)
    {
        return $key === null ? $_POST : $_POST[$key];
    }

    /**
     * Get data from global $_GET with $key according urldecode(). Like urldecode($_GET[$key])
     * @param string $key
     * @return string|null
     */
    public function get($key = null)
    {
        return $key === null ? $_GET : urldecode($_GET[$key]);
    }

    /**
     * Search entery's in string $where by string $what
     * @param $what
     * @param $where
     * @return bool
     */
    public function contains($what, $where)
    {
        return strpos($where, $what) !== false ? true : false;
    }

    /**
     * Search entery's in $where by $suffix. Example: suffixEquals('helloThisWorld', 'World') return true, suffixEquals('helloThisWorld', 'This') - return false
     * @param $where
     * @param $suffix
     * @return bool
     */
    public function suffixEquals($where, $suffix)
    {
        if (strlen($suffix) < 1)
            return false;
        $pharse_suffix = substr($where, -strlen($suffix));
        return $pharse_suffix == $suffix ? true : false;
    }

    /**
     * Like suffixEquals(). Is DEPRICATED!
     * @param string $where
     * @param string $extension
     * @return bool
     * @deprecated
     */
    public function extensionEquals($where, $extension)
    {
        return $this->suffixEquals($where, $extension);
    }

    /**
     * Search entery's int $where by $prefix. Example: prefixEquals('helloThisWorld', 'hello') will return true, prefixEquals('helloThisWorld', 'This') - return false
     * @param string $where
     * @param string $prefix
     * @return bool
     */
    public function prefixEquals($where, $prefix)
    {
        if (strlen($prefix) < 1)
            return false;
        $pharse_prefix = substr($where, 0, strlen($prefix));
        return $pharse_prefix == $prefix ? true : false;
    }

    /**
     * Remove last object after dot (.exe .html etc)
     * @param string $var
     * @return string
     */
    public function noextention($var)
    {
        $split = explode(".", $var);
        array_pop($split);
        return $this->altimplode('', $split);
    }


    /**
     * Safe HTML with allowed tags in $allowed. Example: safeHtml("<p><img src='' />Data text</p>", "<p><img>");
     * @param array|string $data
     * @param string $allowed
     * @return array|string
     */
    public function safeHtml($data, $allowed = '')
    {
        if(is_array($data)) {
            $output = array();
            foreach($data as $key=>$entery)
            {
                $output[$key] = $this->safeHtml($entery, $allowed);
            }
            return $output;
        }
        $unsafe_attributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        return preg_replace('/<(.*?)>/ie', "'<' . preg_replace(array('/javascript:[^\"\']*/i', '/(" . implode('|', $unsafe_attributes) . ")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", strip_tags($data, $allowed));
    }

    /**
     * Remove html entery
     * @param string $data
     * @param bool $save_quotes
     * @return string|array
     */
    public function nohtml($data, $save_quotes = false)
    {
        if(is_array($data)) {
            $output = array();
            foreach($data as $key=>$entery)
            {
                $output[$key] = $save_quotes === true ? strip_tags($entery) : htmlentities(strip_tags($entery), ENT_QUOTES, "UTF-8");
            }
            return $output;
        }
        return $save_quotes === true ? strip_tags($data) : htmlentities(strip_tags($data), ENT_QUOTES, "UTF-8");
    }

    public function stringInline($data) {
        return preg_replace('/\s+/', ' ', trim($data));
    }

    /**
     * Pseudo random [A-Za-z0-9] string with length $length
     * @param int $length
     * @return string
     */
    public function randomString($length)
    {
        $ret = 97;
        $out = null;
        for ($i = 0; $i < $length; $i++) {
            $offset = rand(0, 15);
            $char = chr($ret + $offset);
            $posibility = rand(0, 2);
            if ($posibility == 0) {
                // 33% - подмешиваем случайное число
                $out .= rand(0, 9);
            } elseif ($posibility == 1) {
                // 33% - поднимаем в верхний регистр из offset+ret
                $out .= strtoupper($char);
            } else {
                $out .= $char;
            }
        }
        return $out;
    }

    /**
     * Random Integer with $sequence. Ex: randomInt(2) = 1..9 * 10 ^ 2
     * @param Integer $sequence - показатель длины случайного числа
     * @return number
     */
    public function randomInt($sequence)
    {
        $start = pow(10, $sequence - 1);
        $end = pow(10, $sequence);
        return rand($start, $end);
    }

    /**
     * Random md5 hash based on function randomString
     * $min and $max - base string length
     * @param int $min
     * @param int $max
     * @return string
     */
    public function md5random($min = 16, $max = 20)
    {
        return md5($this->randomString(rand($min, $max)));
    }

    /**
     * Generate random 32..128char string what can be used in urls and posts,
     * example in register aprove, recovery aprove, etc.
     * Function return case sensive result.
     * @return string
     */
    public function randomSecureString128() {
        return $this->randomString(rand(16,64)).$this->randomString(rand(16,64));
    }

    /**
     * Random string(32char length) according unique $data
     * @param string $data
     * @param int $min
     * @param int $max
     * @return string
     */
    public function randomWithUnique($data, $min = 16, $max = 30)
    {
        $offset_min = $min - strlen($data);
        $offset_max = $max - strlen($data);
        if ($offset_max < 0) {
            return $this->md5random();
        } elseif ($offset_min < 0) {
            $data .= $this->randomString(rand(1, $offset_max));
        } else {
            $data .= $this->randomString(rand($offset_min, $offset_max));
        }
        return md5($data);
    }

    /**
     * Redirect user on website. Example : system::getInstance()->redirect("/page.html");
     * @param string $uri
     */
    public function redirect($uri = null)
    {
        if(loader === 'back')
            header("Location: ".property::getInstance()->get('script_url').$uri);
        else
            header("Location: ".property::getInstance()->get('url').$uri);
        exit();
    }

    /**
     * Check is $data latin or numeric string
     * @param string $data
     * @return bool
     */
    public function isLatinOrNumeric($data)
    {
        return !preg_match('/[^A-Za-z0-9_]/s', $data) && $this->length($data) > 0;
    }

    /**
     * String length according UTF-8
     * @param string $data
     * @return number
     */
    public function length($data)
    {
        return mb_strlen($data, "UTF-8");
    }

    /**
     * Alternative function substr according UTF-8 support
     * @param string $data
     * @param int $start
     * @param int $length
     * @return string
     */
    public function altsubstr($data, $start, $length)
    {
        return mb_substr($data, $start, $length, "UTF-8");
    }

    /**
     * Sub string to $length before first space detected
     * @param string $sentence
     * @param int $length
     * @return string
     */
    public function sentenceSub($sentence, $length)
    {
        if($this->length($sentence) <= $length)
            return $sentence;
        $end_point = mb_strpos($sentence, " ", $length, "UTF-8");
        return $this->altsubstr($sentence, 0, $end_point);
    }

    /**
     * Transfer string $data to integer type
     * @param string $data
     * @return int
     */
    public function toInt($data)
    {
        $result = preg_replace('/[^0-9]/s', '', $data);
        return $result < 0 ? 0 : $result;
    }

    /**
     * Check $data is in rage 0-9 (integer)
     * @param string $data
     * @return boolean
     */
    public function isInt($data)
    {
        return !preg_match('/[^0-9]/s', $data) && $this->length($data) > 0;
    }

    /**
     * Check $data is "integer string list", example - 1,2,3,8,25,91,105
     * @param string $data
     * @return boolean
     */
    public function isIntList($data)
    {
        return !preg_match('/[^0-9, ]/s', $data) && $this->length($data) > 0;
    }

    /**
     * Prepare int list or int array to int array, removing < 1 values
     * @param string|array $data
     * @return array
     */
    public function removeNullFrontIntList($data) {
        $new_data = array();
        if(!is_array($data)) {
            $data = $this->altimplode(',', $data);
        }
        foreach($data as $key=>$value) {
            $new_data[$key] = $value;
        }
        return $new_data;
    }

    /**
     * Remove from $array data $value (not a key!)
     * @param string $value
     * @param array $array
     * @return array:
     */
    public function valueUnsetInArray($value, $array)
    {
        return array_values(array_diff($array, array($value)));
    }

    /**
     * Alternative implode (without $decimal on end or start, remove null objects)
     * @param float $decimal
     * @param array $array
     * @return string|null
     */
    public function altimplode($decimal, $array)
    {
        $array = $this->nullArrayClean($array);
        if (!is_array($array)) {
            return null;
        }
        $output = null;
        // exclude last element
        for ($i = 0; $i < sizeof($array) - 1; $i++) {
            $output .= $array[$i] . $decimal;
        }
        $output .= $array[sizeof($array) - 1];
        return $output;
    }

    /**
     * Alternative explode split $deciaml remove null items
     * @param string $decimal
     * @param string $string
     * @return array|null
     */
    public function altexplode($decimal, $string)
    {
        $array = explode($decimal, $string);
        return $this->nullArrayClean($array);
    }

    /**
     * Remove null elements from array. Index key is not saved.
     * @param array $array
     * @return array
     */
    public function nullArrayClean($array)
    {
        if(sizeof($array) < 1)
            return array();
        $outarray = array();
        foreach ($array as $values) {
            if ($values != null && $values != '') {
                $outarray[] = $values;
            }
        }
        return $outarray;
    }

    /**
     * Add item in array if it not detected always in it
     * @param string $item
     * @param array $array
     * @return array
     */
    public function arrayAdd($item, $array)
    {
        if (!in_array($item, $array)) {
            $array[] = $item;
        }
        return $array;
    }

    /**
     * Extract from array 2nd level elements by key ([0] => array(a => b), [1] => array(c=>d) ... n)
     * @param string $key_name
     * @param array $array
     * @return array
     */
    public function extractFromMultyArray($key_name, $array)
    {
        $output = array();
        foreach ($array as $item) {
            $object = $item[$key_name];
            if (!in_array($object, $output))
                $output[] = $item[$key_name];
        }
        return $output;
    }

    /**
     * Transfer posible object to standart date format. Formats : d = d.m.Y, h = d.m.Y hh:mm, s = d.m.Y hh:mm:ss.
     * Allow to use object unix timestamp
     * @param string|DateTime|int $object
     * @param string $out_format
     * @return string
     */
    public function toDate($object, $out_format)
    {
        $result = null;
        if ($this->isInt($object)) {
            $object = date('d.m.Y H:i:s', $object);
        }
        $date_object = new \DateTime($object);
        switch ($out_format) {
            case "h":
                $result = $date_object->format('d.m.Y H:i');
                break;
            case "s":
                $result = $date_object->format('d.m.Y H:i:s');
                break;
            default:
                $result = $date_object->format('d.m.Y');
                break;
        }
        return $result;
    }

    /**
     * Transfer date formats to Unix Time epoch
     * @param string $object
     * @return number
     */
    public function toUnixTime($object)
    {
        return strtotime($object);
    }

    /**
     * Generate array with values from $start to $end
     * @param int $start
     * @param int $end
     * @return array
     */
    public function generateIntRangeArray($start, $end)
    {
        $output = array();
        for ($start; $start <= $end; $start++) { // pos use foreach(range($start,$end) as $item)
            $output[] = $start;
        }
        return $output;
    }

    /**
     * Check string as phone format
     * @param string|int $phone
     * @return boolean
     */
    public function validPhone($phone)
    {
        return preg_match('/^[+]?([\d]{0,3})?[\(\.\-\s]?([\d]{3})[\)\.\-\s]*([\d]{3})[\.\-\s]?([\d]{4})$/', $phone) ? true : false;
    }

    /**
     * Check password length
     * @param string|array $password
     * @return boolean
     */
    public function validPasswordLength($password)
    {
        if (is_array($password)) {
            foreach ($password as $item) {
                if (strlen($item) < 4 || strlen($item) > 32)
                    return false;
            }
            return true;
        } else {
            return (strlen($password) >= 4 && strlen($password) <= 32) ? true : false;
        }
    }

    /**
     * Get current user IP. Allow CDN cloudflare transaction
     * @return string
     */
    public function getRealIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"]))
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        return $ip;
    }

    /**
     * Get double md5 crypt from $string according $salt
     * @param string $string
     * @param string|null $custom_salt
     * @return string
     */
    public function doublemd5($string, $custom_salt = null)
    {
        $salt = property::getInstance()->get('password_salt');
        if($custom_salt != null) {
            $salt = $custom_salt;
        }
        return md5(md5($string) . $salt);
    }

    /**
     * Generate string list from array to SQL query's. Example: array('length' => '15', 'color' => 'red') transfered in: `length` = '15', `color` = 'red'
     * @param array $keyArray
     * @return String
     */
    public function prepareKeyDataToDbUpdate($keyArray)
    {
        $outstring = null;
        $index = 1;
        foreach ($keyArray as $column => $data) {
            $outstring .= "`$column` = '$data'";
            if ($index != sizeof($keyArray)) {
                $outstring .= ", ";
            }
            $index++;
        }
        return $outstring;
    }

    /**
     * Generate from $array string list 'a1', 'a2', 'a3', ... 'an' for SQL query's
     * @param $array
     * @return null|string
     */
    public function DbPrepareListdata($array)
    {
        $output = null;
        $i = 1;
        foreach($array as $value) {
            // last object
            if(sizeof($array) == $i) {
                $output .= "'{$value}'";
            } else {
                $output .= "'{$value}', ";
            }
            $i++;
        }
        return $output;
    }

    /**
     * Validate IP
     * @param $ip
     * @return boolean
     */
    public function validIP($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Remove from string $string string $char $count number of times
     * @param string $char
     * @param string $string
     * @param int $count
     * @return string|null
     */
    public function removeCharsFromString($char, $string, $count) {
        return preg_replace('#('.$char.')#', '',$string, $count);
    }

    /**
    * Get the directory size in byte's
    * @param string $directory
    * @return integer
    */
    public function getDirSize($directory) {
        $size = 0;
        if(file_exists($directory)) {
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file){
                $size+=$file->getSize();
            }
        }
        return $size;
    }

    /**
     * Remove directory with all files inside.
     * @param $dir
     */
    public function removeDirectory($dir) {
        if(file_exists($dir)) {
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path) {
                $path->isFile() ? @unlink($path->getPathname()) : @rmdir($path->getPathname());
            }
            @rmdir($dir);
        }
    }

    /**
     * Create directory with htaccess "deny from all"
     * @param $dir
     * @param int $chmod
     */
    public function createPrivateDirectory($dir, $chmod = 0755) {
        $this->createDirectory($dir, $chmod);
        $protect = "deny from all";
        @file_put_contents($dir .'.htaccess', $protect);
    }

    /**
     * Create directory in CMS root folder using path. Recursive is supported.
     * Example: system::getInstance()->createDirectory(root . '/upload/test/testother/')
     * @param $path
     * @param int $chmod
     */
    public function createDirectory($path, $chmod = 0755) {
        if(!$this->prefixEquals($path, root))
            $path = root . $path;
        if(file_exists($path))
            return;
        @mkdir($path, $chmod, true);
    }

    /**
     * Save file data with creating path if not founded. Extended alias to file_put_contents
     * @param string $file_data
     * @param string $path
     */
    public function putFile($file_data, $path) {
        $path_array = explode('/', $path);
        array_pop($path_array);
        $path_dir = implode('/', $path_array);
        if(!$this->prefixEquals($path_dir, root))
            $path = root . $path_dir;
        if(!file_exists($path_dir))
            $this->createDirectory($path_dir);
        @file_put_contents($path, $file_data);
    }

    /**
     * Alias function for putFile($file_name, $path)
     * @param string $file_data
     * @param string $path
     */
    public function saveFile($file_data, $path) {
        $this->putFile($file_data, $path);
    }

    /**
     * Alias function for putFile($file_name, $path)
     * @param string $file_data
     * @param string $path
     */
    public function storeFile($file_data, $path) {
        $this->putFile($file_data, $path);
    }

    /**
     * Get content from URL according curl
     * @param string $url
     * @return string
     */
    public function url_get_contents($url)
    {
        $content = null;
        if(function_exists('curl_version')) {
            $curl = \curl_init();
            $userAgent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';

            \curl_setopt($curl,CURLOPT_URL, $url);
            \curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
            \curl_setopt($curl,CURLOPT_CONNECTTIMEOUT, 5);

            \curl_setopt($curl, CURLOPT_HEADER, 0);
            \curl_setopt($curl, CURLOPT_USERAGENT, $userAgent);
            \curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);
            \curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            \curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);
            \curl_setopt($curl, CURLOPT_TIMEOUT, 10);

            $content = \curl_exec($curl);
            \curl_close($curl);
        } else {
            $content = @file_get_contents($url);
        }
        return $content;
    }

    /**
     * Scan directory for files and directory's
     * @param string $dir
     * @param bool $sort_desc
     * @return array
     */
    public function altscandir($dir, $sort_desc = false) {
        $ignored = array('.', '..', '.svn', '.htaccess');

        $files = array();
        foreach (scandir($dir) as $file) {
            if(in_array($file, $ignored)) continue;
            $files[$file] = filemtime($dir . '/' . $file);
        }
        if($sort_desc)
            arsort($files);
        else
            asort($files);

        $files = array_keys($files);

        return $files;
    }

    /**
     * Add slashes to string or array data to save json or serialize or other response.
     * @param string|array $data
     * @return array|string
     */
    public function altaddslashes($data) {
        if(is_array($data)) {
            $output = array();
            foreach($data as $key => $val){
                if(is_array($key)) {
                    return $this->altaddslashes($key);
                } else {
                    $output[$key] = addslashes($val);
                }
            }
            return $output;
        } else {
            return addslashes($data);
        }
    }

    /**
     * Remove slashes from string or array.
     * @param string|array $data
     * @return array|string
     */
    public function altstripslashes($data) {
        if(is_array($data)) {
            $output = array();
            foreach($data as $key => $val){
                if(is_array($key)) {
                    return $this->altstripslashes($key);
                } else {
                    $output[$key] = stripslashes($val);
                }
            }
            return $output;
        } else {
            return stripslashes($data);
        }
    }

    public function altupper($text) {
        return mb_strtoupper($text, 'UTF-8');
    }

    public function altlower($text) {
        return mb_strtolower($text, 'UTF-8');
    }

    /**
     * Convert many &amp;amp;quotes; to " as example
     * @param string $text
     * @return string
     */
    public function htmlQuoteDecode($text) {
        $text = str_replace('&amp;', '&', $text);
        if(strpos($text, '&amp;'))
            return $this->htmlQuoteDecode($text);
        return html_entity_decode($text, ENT_QUOTES | ENT_IGNORE, "UTF-8");
    }

    /**
     * Get file mime type based on finfo function or gd lib (only for images)
     * @param string $file
     * @return null|string
     */
    public function getMime($file) {
        if(function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_NONE | FILEINFO_MIME);
            $mime = strstr(finfo_file($finfo, $file), ';', true); // ex: image/jpeg; charset=binary to image/jpeg
            finfo_close($finfo);
            return $mime;
        } elseif(function_exists('mime_content_type')) {
            return mime_content_type($file);
        } elseif(function_exists('getimagesize')) { // no other way, only gd func
            $info = getimagesize($file);
            return $info['mime'];
        } else {
            logger::getInstance()->log(logger::LEVEL_ERR, 'Not founded system function to get Mime info. Please install finfo extension.');
            return null;
        }

    }
}