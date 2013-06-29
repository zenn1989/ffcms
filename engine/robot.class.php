<?php

/**
 * Класс занимающийся сбором и обработкой статистических данных.
 * @author zenn
 *
 */
class robot
{
    // Сбор статистики о текущем пользователе
    public function collect()
    {
        global $database, $constant, $system, $user;
        $realip = $system->getRealIp();
        $visittime = time();
        $browser = $this->user_browser($_SERVER['HTTP_USER_AGENT']);
        $os = $this->user_os($_SERVER['HTTP_USER_AGENT']);
        $cookie = $_COOKIE['source'];
        $isreg = $user->get('id') < 1 ? 0 : 1;
        $userid = $user->get('id') < 1 ? 0 : $user->get('id');
        if ($cookie == null) {
            $settime = $visittime + (365 * 24 * 60 * 60);
            setcookie('source', $system->md5random(), $settime, '/');
            $cookie = '';
        }
        $referer = $_SERVER['HTTP_REFERER'] == null ? '' : $_SERVER['HTTP_REFERER'];
        $path = $_SERVER['REQUEST_URI'];
        $query = "INSERT INTO {$constant->db['prefix']}_statistic (ip, cookie, browser, os, time, referer, path, reg_id) VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $database->con()->prepare($query);
        $stmt->bindParam(1, $realip, PDO::PARAM_STR);
        $stmt->bindParam(2, $cookie, PDO::PARAM_STR, 32);
        $stmt->bindParam(3, $browser, PDO::PARAM_STR);
        $stmt->bindParam(4, $os, PDO::PARAM_STR);
        $stmt->bindParam(5, $visittime, PDO::PARAM_INT);
        $stmt->bindParam(6, $referer, PDO::PARAM_STR);
        $stmt->bindParam(7, $path, PDO::PARAM_STR);
        $stmt->bindParam(8, $userid, PDO::PARAM_INT);
        $stmt->execute();

    }

    // Функция определения Браузера+версии
    // Взята из публичного доступа. (c) Гельтищева Нина.
    private function user_browser($agent)
    {
        preg_match("/(MSIE|Opera|Firefox|Chrome|Version|Opera Mini|Netscape|Konqueror|SeaMonkey|Camino|Minefield|Iceweasel|K-Meleon|Maxthon)(?:\/| )([0-9.]+)/", $agent, $browser_info);
        list(, $browser, $version) = $browser_info;
        if (preg_match("/Opera ([0-9.]+)/i", $agent, $opera)) return 'Opera ' . $opera[1];
        if ($browser == 'MSIE') {
            preg_match("/(Maxthon|Avant Browser|MyIE2)/i", $agent, $ie);
            if ($ie) return $ie[1] . ' ' . $version;
            return 'IE ' . $version;
        }
        if ($browser == 'Firefox') {
            preg_match("/(Flock|Navigator|Epiphany)\/([0-9.]+)/", $agent, $ff);
            if ($ff) return $ff[1] . ' ' . $ff[2];
        }
        if ($browser == 'Opera' && $version == '9.80') return 'Opera ' . substr($agent, -5);
        if ($browser == 'Version') return 'Safari ' . $version;
        if (!$browser && strpos($agent, 'Gecko')) return 'Gecko';
        return $browser . ' ' . $version;
    }

    private function user_os($agent)
    {
        if (preg_match('/windows|win32/i', $agent)) {
            return 'windows';
        } elseif (preg_match('/linux|unix/i', $agent)) {
            return "nix";
        } elseif (preg_match('/macintosh|mac os/i', $agent)) {
            return "mac";
        } else {
            return "unknown";
        }
    }

}

?>