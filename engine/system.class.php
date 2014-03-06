<?php
/**
 * Copyright (C) 2013 ffcms software, Pyatinskyi Mihail
 *
 * FFCMS is a free software developed under GNU GPL V3.
 * Official license you can take here: http://www.gnu.org/licenses/
 *
 * FFCMS website: http://ffcms.ru
 */
namespace engine;
use DateTime;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class system extends singleton {

    protected static $instance = null;

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
     * Получение входящей переменной $_POST по значению $key с отбросом {$params} от пользователя
     * @param null $key
     * @return array|mixed
     */
    public function post($key = null)
    {
        return $key === null ? $_POST : $this->noParam($_POST[$key]);
    }

    /**
     * Получение входящих данных из $_GET строки по значению $key с использованием urldecode()
     * @param $key
     * @return string
     */
    public function get($key = null)
    {
        return $key === null ? $_GET : urldecode($_GET[$key]);
    }


    /**
     * Замена глобальных переменных на сущности ANSI там, где они не нужны(USER INPUT данные). Т.к. сущесвуют методы, позволяющие работать
     * в суперпозиции - необходимо очистить вводимый пользователем контент от {$vars} в целях безопасности.
     * @param string $data
     * @return mixed
     */
    public function noParam($data)
    {
        // если это multiarray $_POST['key1']['key2']['keyN']
        if (is_array($data)) {
            $output_data = array();
            foreach ($data as $key => $value) {
                // это еще 1 уровень вложенности, используем рекурсию
                if (is_array($value)) {
                    $output_data[$key] = $this->noParam($value);
                } else {
                    $output_data[$key] = $this->stringNoParam($value);
                }
            }
            return $output_data;
        }
        return $this->stringNoParam($data);
    }

    private function stringNoParam($data)
    {
        preg_match_all('/{\$(.*?)}/i', $data, $matches, PREG_PATTERN_ORDER);
        foreach ($matches[1] as $clear) {
            $data = preg_replace('/{\$(.*?)}/i', "&#123;&#036;$clear&#125;", $data, 1);
        }
        return $data;
    }

    /**
     * Функция поиска вхождений в $where по ключу $what
     * @param $what
     * @param $where
     * @return bool
     */
    public function contains($what, $where)
    {
        return strpos($where, $what) !== false ? true : false;
    }

    /**
     * Поиск вхождений с конца строки для $suffix по его длине. Пример: suffixEquals(helloThisWorld, World) вернет true, suffixEquals(helloThisWorld, This) - вернет false
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
     * Функция обвертка для suffixEquals
     * @param $where
     * @param $extension
     * @return bool
     * @deprecated
     */
    public function extensionEquals($where, $extension)
    {
        return $this->suffixEquals($where, $extension);
    }

    /**
     * Поиск вхождений с начала строки для $prefix по его длине. Пример: prefixEquals(helloThisWorld, hello) вернет true, prefixEquals(helloThisWorld, This) - вернет false
     * @param $where
     * @param $prefix
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
     * Удаляет расширение у $var (indexxxx.html => index, vasya.exe => vasya)
     * Не спасет от идиотизма вида index.html.html.ht.html.ml но нам это и не нужно.
     */
    public function noextention($var)
    {
        $split = explode(".", $var);
        array_pop($split);
        return $this->altimplode('', $split);
    }


    /**
     * Безопасный html. Применять к входящим данным от пользователя.
     */
    public function safeHtml($data, $allowed = '')
    {
        $unsafe_attributes = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavaible', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragdrop', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterupdate', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmoveout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        return preg_replace('/<(.*?)>/ie', "'<' . preg_replace(array('/javascript:[^\"\']*/i', '/(" . implode('|', $unsafe_attributes) . ")[ \\t\\n]*=[ \\t\\n]*[\"\'][^\"\']*[\"\']/i', '/\s+/'), array('', '', ' '), stripslashes('\\1')) . '>'", strip_tags($data, $allowed));
    }

    /**
     * Удаление html тегов
     */
    public function nohtml($data)
    {
        if(is_array($data)) {
            $output = array();
            foreach($data as $key=>$entery)
            {
                $output[$key] = strip_tags($entery);
            }
            return $output;
        }
        return strip_tags($data);
    }

    /**
     * Псевдо-случайная A-Za-z0-9 строка с заданной длиной
     * Алгоритм достаточно устойчив к бруту, если его длина не менее 16 символов
     * Однако, для токенов или подобных алгоритмов, рекомендуем функцию md5random()
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
     * Случайный Integer
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
     * Случайный md5-хеш на основе функции randomString
     * $min и $max - показатели для выборки случайного размера исходной строки
     */
    public function md5random($min = 16, $max = 20)
    {
        return md5($this->randomString(rand($min, $max)));
    }

    /**
     * Generate random 32..128char string what can be used in urls and posts,
     * example in register aprove, recovery aprove, etc.
     * Function return case sensive result.
     */
    public function randomSecureString128() {
        return $this->randomString(rand(16,64)).$this->randomString(rand(16,64));
    }

    /**
     * Случайная величина отталкиваясь от уникального значения $data
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
     * Перенаправление пользователей, обязателен корень /
     */
    public function redirect($uri = null)
    {
        if(loader === 'back')
            header("Location: ".property::getInstance()->get('script_url').$uri);
        else
            header("Location: ".property::getInstance()->get('url').$uri);
        exit();
    }

    public function isLatinOrNumeric($data)
    {
        return !preg_match('/[^A-Za-z0-9_]/s', $data) && $this->length($data) > 0;
    }

    /**
     * Длина строки с корректной обработкой UTF-8
     * @param int $data
     * @return number
     */
    public function length($data)
    {
        return mb_strlen($data, "UTF-8");
    }

    /**
     * Альтернативный substr с учетом UTF-8 символики
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
     * Обрезка предложения до плавающей длины $length до вхождения первого пробела
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
     * Приведение $data к Integer
     * @param string $data
     * @return int
     */
    public function toInt($data)
    {
        $result = preg_replace('/[^0-9]/s', '', $data);
        return $result < 0 ? 0 : $result;
    }

    /**
     * Проверка $data на принадлежность к диапазону 0-9
     * @param boolean $data
     * @return boolean
     */
    public function isInt($data)
    {
        return !preg_match('/[^0-9]/s', $data) && $this->length($data) > 0;
    }

    /**
     * Специфическая проверка на принадлежность $data к "integer string list", к примеру - 1,2,3,8,25,91,105
     * @param string $data
     * @return boolean
     */
    public function isIntList($data)
    {
        return !preg_match('/[^0-9,]/s', $data) && $this->length($data) > 0;
    }

    /**
     * Удаляет из массива $array значение $value (не ключ!)
     * @param string $value
     * @param array $array
     * @return array:
     */
    public function valueUnsetInArray($value, $array)
    {
        return array_values(array_diff($array, array($value)));
    }

    /**
     * Функция альтернативного имплода массива в адекватную строку (без $decimal в конце или первым элементом, отброс null елементов)
     * @param float $decimal
     * @param array $array
     * @return array|null
     */
    public function altimplode($decimal, $array)
    {
        $array = $this->nullArrayClean($array);
        if (!is_array($array)) {
            return null;
        }
        $output = null;
        // перебираем исключая последний элемент
        for ($i = 0; $i < sizeof($array) - 1; $i++) {
            $output .= $array[$i] . $decimal;
        }
        $output .= $array[sizeof($array) - 1];
        return $output;
    }

    /**
     * Альтернативное разрезание строки по $deciaml и отбросом null элементов
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
     * Отбрасывание null-элементов из массива. Индекс массива не сохраняется.
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
     * Добавление элемента в массив если такой элемент уже НЕ содержиться в массиве.
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
     * Вытаскивание из массива 2го уровня значения ключа с учетом того что массив содержит ряд элементов 2го уровня ([0] => array(a => b), [1] => array(c=>d) ... n)
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
     * Преобразование популярных форматов даты в 1 формат отображения. Формат - d = d.m.Y, h = d.m.Y hh:mm, s = d.m.Y hh:mm:ss.
     * Так же принимаются значения unix time.
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
     * Приведение форматов дат к представлению Unix Time epoche
     * @param array $object
     * @return number
     */
    public function toUnixTime($object)
    {
        return strtotime($object);
    }

    public function generateIntRangeArray($start, $end)
    {
        $output = array();
        for ($start; $start <= $end; $start++) {
            $output[] = $start;
        }
        return $output;
    }

    /**
     * Проверка формата строки на пренадлежность к телефонному номеру.
     * @param string|int $phone
     * @return boolean
     */
    public function validPhone($phone)
    {
        return preg_match('/^[+]?([\d]{0,3})?[\(\.\-\s]?([\d]{3})[\)\.\-\s]*([\d]{3})[\.\-\s]?([\d]{4})$/', $phone) ? true : false;
    }

    /**
     * Валидность длины пароля. В дальнейшем вынести в конфиг.
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
     * Получение IP-адресса пользователя с учетом возможных проксей и CDN
     * @return string
     */
    public function getRealIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        // адаптация для cloudflare
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            // переопределяем
            $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        return $ip;
    }

    /**
     * Получение двойного MD5 хеша от строки $string с использованием неслучайной уникальной соли.
     * @param string $string
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
     * Генерация строки для SQL запроса. Пример: array('length' => '15', 'color' => 'red') будет приобразовано в: `length` = '15', `color` = 'red'
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
     * Генерация из массива $array списка 'a1', 'a2', 'a3', ... 'an' для SQL запросов
     * @param $array
     * @return null|string
     */
    public function DbPrepareListdata($array)
    {
        $output = null;
        $i = 1;
        foreach($array as $value) {
            // последний элемент
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
     * Проверка IP на валидность
     * @param $ip
     * @return boolean
     */
    public function validIP($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }

    /**
     * Удаление пробелов с начала и конца строки. Пример String ' Hello ' => 'Hello'
     * @param $string
     * @return string
     */
    public function noSpaceOnStartEnd($string) {
        $start_character = substr($string, 0, 1);
        if($start_character == " ") {
            $string = substr($string, 1, strlen($string));
        }
        $end_character = substr($string, -1);
        if($end_character == " ") {
            $string = substr($string, 0, -1);
        }
        return $string;
    }

    /**
     * Удаление из строки $string параметра $char $count-количество (не удаление всех, а до указанного порядкового следования)
     * @param $char
     * @param $string
     * @param $count
     */
    public function removeCharsFromString($char, $string, $count) {
        return preg_replace('#('.$char.')#', '',$string, $count);
    }

    /**
    * Get the directory size in byte's
    * @param directory $directory
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
}