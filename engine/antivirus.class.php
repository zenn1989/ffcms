<?php

/**
 * Класс отвечающий за проверку одиночных файлов и всех файлов системы на наличие вредоностного кода.
 * Class antivirus
 */
class antivirus
{
    // ('dir/dir2/file.ext' => md5_size())
    private $scan_md5 = array();
    private $version_md5 = array();
    private $success_check_md5 = array();
    private $notexist_check_md5 = array();
    private $wrong_check_md5 = array();
    private $hack_check = array();

    public function doFullScan()
    {
        global $system;
        $this->loadVersionMd5List();
        $this->recursiveScanDir();
        // файл исходных контрольных сумм не скомпроментирован
        if(sizeof($this->version_md5) > 0)
        {
            foreach($this->scan_md5 as $found_file=>$md5_file)
            {
                // TODO: добавить чекер на JS
                if($system->suffixEquals($found_file, '.php') || $system->suffixEquals($found_file, '.phtml') || $system->contains('.php', $found_file))
                {
                    if($this->version_md5[$found_file] == null)
                    {
                        $this->notexist_check_md5[$found_file] = $md5_file;
                    }
                    elseif($this->version_md5[$found_file] != $md5_file)
                    {
                        $this->wrong_check_md5[$found_file] = $md5_file;
                    }
                    else
                    {
                        $this->success_check_md5[$found_file] = $md5_file;
                    }
                }
            }
        }
        $this->scan_md5 = null;
        $this->version_md5 = null;
    }

    private function recursiveScanDir($dir = null)
    {
        global $constant,$system;
        $dir_scan = null;
        if($dir == null)
            $dir_scan = $constant->root.$constant->ds;
        else
            $dir_scan = $constant->root.$constant->ds.$dir.$constant->ds;
        $objects = scandir($dir_scan);
        foreach($objects as $item)
        {
            $md5sum = null;
            if(is_file($dir_scan.$item))
            {
                $md5sum = md5_file($dir_scan.$item);
            }
            elseif(is_dir($dir_scan.$item))
            {
                if(!$system->prefixEquals($item, "."))
                {
                    if($dir == null)
                        $this->recursiveScanDir($item);
                    else
                        $this->recursiveScanDir($dir.'/'.$item);
                }
            }
            if($md5sum != null)
                $this->scan_md5[$dir.'/'.$item] = $md5sum;
        }
    }

    private function loadVersionMd5List()
    {
        global $constant;
        $md5file = $constant->root."/resource/antivirus/.md5sum";
        if(file_exists($md5file))
        {
            $this->version_md5 = unserialize(file_get_contents($md5file));
        }
    }

    public function containsHackMethods($content)
    {
        if(preg_match('/eval\(|exec\(|passthru\(|shell_exec\(|system\(|proc_open\(|popen,curl_exec\(|curl_multi_exec\(|parse_ini_file\(|show_source\(|base64_/s', $content) && !preg_match('/class antivirus/s', $content))
        {
            return true;
        }
        return false;
    }

    public function getClearList()
    {
        return $this->success_check_md5;
    }

    public function getUnknownList()
    {
        global $constant;
        foreach($this->notexist_check_md5 as $dir=>$md5)
        {
            $file_root = file_get_contents($constant->root."/".$dir);
            if($this->containsHackMethods($file_root))
            {
                $this->hack_check[$dir] = $md5;
            }
        }
        return $this->notexist_check_md5;
    }

    public function getWrongList()
    {
        global $constant;
        foreach($this->wrong_check_md5 as $dir=>$md5)
        {
            $file_root = file_get_contents($constant->root."/".$dir);
            if($this->containsHackMethods($file_root))
            {
                $this->hack_check[$dir] = $md5;
            }
        }
        return $this->wrong_check_md5;
    }

    public function getHackList()
    {
        return $this->hack_check;
    }


}


?>