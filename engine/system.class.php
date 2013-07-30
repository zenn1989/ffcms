<?php
// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

class system
{

    public $post_data = array();
    public $get_data = array();

    function system()
    {
        $this->post_data = $_POST;
        $this->get_data = $_GET;
    }

    /**
     * Получение входящей переменной $_POST по значению $key с отбросом {$params} от пользователя
     * @param null $key
     * @return array|mixed
     */
    public function post($key = null)
    {
        return $key == null ? $this->post_data : $this->noParam($this->post_data[$key]);
    }

    /**
     * Получение входящих данных из $_GET строки по значению $key с использованием urldecode()
     * @param $key
     * @return string
     */
    public function get($key)
    {
        return urldecode($this->get_data[$key]);
    }


    /**
     * Замена глобальных переменных на сущности ANSI там, где они не нужны(USER INPUT данные). Т.к. сущесвуют методы, позволяющие работать
     * в суперпозиции - необходимо очистить вводимый пользователем контент от {$vars} в целях безопасности.
     * @param unknown_type $data
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
        global $constant;
        header("Location: {$constant->url}{$uri}");
        exit();
        return;
    }

    public function isLatinOrNumeric($data)
    {
        return !preg_match('/[^A-Za-z0-9_]/s', $data) && $this->length($data) > 0;
    }

    /**
     * Длина строки с корректной обработкой UTF-8
     * @param unknown_type $data
     * @return number
     */
    public function length($data)
    {
        return mb_strlen($data, "UTF-8");
    }

    /**
     * Альтернативный substr с учетом UTF-8 символики
     * @param unknown_type $data
     * @param unknown_type $start
     * @param unknown_type $length
     * @return string
     */
    public function altsubstr($data, $start, $length)
    {
        return mb_substr($data, $start, $length, "UTF-8");
    }

    /**
     * Обрезка предложения до плавающей длины $length до вхождения первого пробела
     * @param unknown_type $sentence
     * @param unknown_type $length
     * @return string
     */
    public function sentenceSub($sentence, $length)
    {
        $end_point = mb_strpos($sentence, " ", $length, "UTF-8");
        return $this->altsubstr($sentence, 0, $end_point);
    }

    /**
     * Приведение $data к Integer
     * @param unknown_type $data
     * @return mixed
     */
    public function toInt($data)
    {
        $result = preg_replace('/[^0-9]/s', '', $data);
        return $result < 0 ? 0 : $result;
    }

    /**
     * Проверка $data на принадлежность к диапазону 0-9
     * @param unknown_type $data
     * @return boolean
     */
    public function isInt($data)
    {
        return !preg_match('/[^0-9]/s', $data) && $this->length($data) > 0;
    }

    /**
     * Специфическая проверка на принадлежность $data к "integer string list", к примеру - 1,2,3,8,25,91,105
     * @param unknown_type $data
     */
    public function isIntList($data)
    {
        return !preg_match('/[^0-9,]/s', $data) && $this->length($data) > 0;
    }

    /**
     * Удаляет из массива $array значение $value (не ключ!)
     * @param unknown_type $value
     * @param unknown_type $array
     * @return multitype:
     */
    public function valueUnsetInArray($value, $array)
    {
        return array_values(array_diff($array, array($value)));
    }

    /**
     * Функция альтернативного имплода массива в адекватную строку (без $decimal в конце или первым элементом, отброс null елементов)
     * @param unknown_type $decimal
     * @param unknown_type $array
     * @return NULL|unknown
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
     * @param unknown_type $decimal
     * @param unknown_type $string
     * @return Ambigous <multitype:unknown, multitype:unknown >
     */
    public function altexplode($decimal, $string)
    {
        $array = explode($decimal, $string);
        return $this->nullArrayClean($array);
    }

    /**
     * Отбрасывание null-элементов из массива. Индекс массива не сохраняется.
     * @param unknown_type $array
     * @return multitype:unknown
     */
    public function nullArrayClean($array)
    {
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
     * @param unknown_type $item
     * @param unknown_type $array
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
     * @param unknown_type $key_name
     * @param unknown_type $array
     * @return Ambigous <NULL, unknown>
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
     * @param unknown_type $object
     * @param unknown_type $out_format
     * @return Ambigous <NULL, string>
     */
    public function toDate($object, $out_format)
    {
        $result = null;
        if ($this->isInt($object)) {
            $object = date('d.m.Y H:i:s', $object);
        }
        $date_object = new DateTime($object);
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
     * @param unknown_type $object
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
     * @param unknown_type $phone
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
     * @return unknown
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
     * @param unknown_type $string
     * @return string
     */
    public function doublemd5($string, $custom_salt = null)
    {
        global $constant;
        $salt = $constant->password_salt;
        if($custom_salt != null) {
            $salt = $custom_salt;
        }
        return md5(md5($string) . $salt);
    }

    /**
     * Генерация строки для SQL запроса. Пример: array('length' => '15', 'color' => 'red') будет приобразовано в: `length` = '15', `color` = 'red'
     * @param unknown_type $keyArray
     * @return Ambigous <NULL, string>
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

}

?>