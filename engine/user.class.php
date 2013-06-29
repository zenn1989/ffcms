<?php

/**
 * Класс отвечающий за пользовательские данные
 */
class user
{
    private $userparam = array();
    private $userindex = null;
    private $custominit = false;
    private $customparam = array();

    function user()
    {
        $this->auth();
    }

    /**
     * Анализ пользовательских данных, установка параметров если пользователь авторизован.
     */
    private function auth()
    {
        global $database, $constant, $system;
        // необходимая пара для анализа авторизован ли пользователь
        // pesonal id может быть как логином так и почтой
        $token = $_COOKIE['token'];
        $personal_id = $_COOKIE['person'];
        // данные удовлетворяют шаблон
        if (strlen($token) == 32 && (filter_var($personal_id, FILTER_VALIDATE_EMAIL) || (strlen($personal_id) > 0 && $system->isLatinOrNumeric($personal_id)))) {
            $query = "SELECT * FROM {$constant->db['prefix']}_user a, {$constant->db['prefix']}_user_access_level b WHERE (a.email = ? OR a.login = ?) AND a.token = ? AND a.aprove = 0 AND a.access_level = b.group_id";
            $stmt = $database->con()->prepare($query);
            $stmt->bindParam(1, $personal_id, PDO::PARAM_STR);
            $stmt->bindParam(2, $personal_id, PDO::PARAM_STR);
            $stmt->bindParam(3, $token, PDO::PARAM_STR, 32);
            $stmt->execute();
            if ($stmt->rowCount() == 1) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ((time() - $result[0]['token_start']) < $constant->token_time) {
                    $this->userindex = $result[0]['id'];
                    foreach ($result[0] as $column_index => $column_data) {
                        $this->userparam[$result[0]['id']][$column_index] = $column_data;
                    }
                    $this->customset($result[0]['id']);
                }
            }
        }
    }

    private function set($id)
    {
        global $database, $constant;
        if (!array_key_exists($id, $this->userparam)) {
            $query = "SELECT * FROM {$constant->db['prefix']}_user a, {$constant->db['prefix']}_user_access_level b WHERE a.id = ? AND a.aprove = 0 AND a.access_level = b.group_id";
            $stmt = $database->con()->prepare($query);
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() == 1) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($result[0] as $column_index => $column_data) {
                    $this->userparam[$result[0]['id']][$column_index] = $column_data;
                }
            }
        }
    }

    /**
     * Получение определенных пользовательских данных - аналогично названию в таблицах user / user_access_level
     * @param unknown_type $param
     * @param unknown_type $id - получение данных другого пользователя.
     */
    public function get($param, $id = 0)
    {
        // это запрос не для текущего авторизованого пользоваетеля.
        if ($id > 0) {
            $this->set($id);
            return $this->userparam[$id][$param];
        } // иначе отдаем данные текущего объекта
        else {
            return $this->userparam[$this->userindex][$param];
        }
    }

    /**
     * Получение дополнительных полей пользовательских данных из таблицы ffcms_user_custom
     * @param unknown_type $param
     * @return multitype:
     */
    public function customget($param, $id = 0)
    {
        if ($id < 1) {
            $id = $this->get('id');
        }
        $this->customset($id);
        return $this->customparam[$id][$param];
    }

    private function customset($id)
    {
        global $database, $constant;
        // если случилась какая то херня или криворукий мудак пишет плагин без проверки oid
        if ($id < 1 || array_key_exists($id, $this->customparam)) {
            return;
        }
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_custom WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            // доп. поля не были инициированы. Инициируем.
            $stmt = null;
            $stmt = $database->con()->prepare("INSERT INTO {$constant->db['prefix']}_user_custom (id) VALUES (?)");
            $stmt->bindParam(1, $id, PDO::PARAM_INT);
            $stmt->execute();
            // рекурсивно переопределяем данные (:
            return $this->customset($id);
        } else {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result[0] as $index => $data) {
                $this->customparam[$id][$index] = $data;
            }
        }

    }

    /**
     * Перезагрузка всех пользовательских данных
     * @param unknown_type $id
     */
    public function fulluseroverload($id)
    {
        $this->useroverload($id);
        $this->customoverload($id);
    }

    /**
     * Перезагрузка основных пользовательских данных если они были изменены в процессе обработки
     * @param unknown_type $id
     */
    public function useroverload($id)
    {
        global $database, $constant;
        if ($id < 1 || !$this->userparam[$id])
            return;
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user a, {$constant->db['prefix']}_user_access_level b WHERE a.id = ? AND a.access_level = b.group_id");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res[0] as $index => $data) {
            $this->userparam[$id][$index] = $data;
        }
    }

    /**
     * Перезагрузка кастомных данных после того как они уже были выгружены и были изменены в процессе работы.
     * @param unknown_type $id
     */
    public function customoverload($id)
    {
        global $database, $constant;
        // если id пуст или 0 или такой параметр еще не выставлен - возврат
        if ($id < 1 || !$this->customparam[$id]) {
            return;
        }
        $stmt = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_custom WHERE id = ?");
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result[0] as $index => $data) {
            $this->customparam[$id][$index] = $data;
        }
    }

    /**
     * Если необходимо в дальнейшем использовать данные большого числа пользователей
     * мы рекомендуем выгрузить их с помощью данного метода, дабы не выгружать каждого пользователя отдельным SQL запросом
     * @param String $list - example 1,2,5,19,201
     */
    public function listload($list)
    {
        global $database, $constant, $system;
        if (is_array($list)) {
            $list = $system->altimplode(',', $list);
        }
        if (!$system->isIntList($list) || strlen($list) < 1) {
            return;
        }
        // это запросто делается и 1 запросом, однако разграничить как-либо дефалт и кустом параметры - невозможно, а делать жесткую привязку к колонкам еще больший дебилизм
        $stmt1 = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user WHERE id in($list)");
        $stmt2 = $database->con()->prepare("SELECT * FROM {$constant->db['prefix']}_user_custom WHERE id in($list)");
        $stmt1->execute();
        $stmt2->execute();
        $result_list_standart = $stmt1->fetchAll(PDO::FETCH_ASSOC);
        $result_list_custom = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        //var_dump($result_list_standart);
        foreach ($result_list_standart as $array_data) {
            foreach ($array_data as $key => $data) {
                $this->userparam[$array_data['id']][$key] = $data;
            }
        }
        foreach ($result_list_custom as $array_data) {
            foreach ($array_data as $key => $data) {
                $this->customparam[$array_data['id']][$key] = $data;
            }
        }
    }

    /**
     * Существует ли пользователь, по ID
     */
    public function exists($userid)
    {
        global $database, $constant;
        $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user WHERE id = ?");
        $stmt->bindParam(1, $userid, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result[0];
    }

    /**
     * Построение имени аватара пользователя. Вернет noavatar.jpg в случае отсутствия.
     * @param unknown_type $type
     * @param unknown_type $userid
     * @return string
     */
    public function buildAvatar($type = "small", $userid)
    {
        global $constant;
        $filepath = $constant->root . "/upload/user/avatar/$type/avatar_$userid.jpg";
        if (!file_exists($filepath)) {
            return "noavatar.jpg";
        }
        // делаем динамический ?mtime=время чтобы кеширование браузера работало верно для измененных аватаров
        $filetime = filemtime($filepath);
        return "avatar_$userid.jpg?mtime=$filetime";
    }

    public function isPermaBan()
    {
        global $database, $constant, $system;
        $stmt = null;
        $ip = $system->getRealIp();
        $time = time();
        $userid = $this->get('id');
        if ($userid > 0) {
            $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user_block WHERE (user_id = ? or ip = ?) AND express > ?");
            $stmt->bindParam(1, $userid, PDO::PARAM_INT);
            $stmt->bindParam(2, $ip, PDO::PARAM_STR);
            $stmt->bindParam(3, $time, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $database->con()->prepare("SELECT COUNT(*) FROM {$constant->db['prefix']}_user_block WHERE ip = ? AND express > ?");
            $stmt->bindParam(1, $ip, PDO::PARAM_STR);
            $stmt->bindParam(2, $time, PDO::PARAM_INT);
            $stmt->execute();
        }
        $rowFetch = $stmt->fetch();
        $count = $rowFetch[0];
        return $count > 0 ? true : false;
    }
}

?>