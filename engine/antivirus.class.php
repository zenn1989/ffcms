<?php

// --------------------------------------//
// THIS SOFTWARE USE GNU GPL V3 LICENSE //
// AUTHOR: zenn, Pyatinsky Mihail.     //
// Official website: www.ffcms.ru     //
// ----------------------------------//

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
    private $excluded_directory = array();

    public function doFullScan()
    {
        global $engine;
        $this->loadVersionMd5List();
        $this->loadExcludedDirectory();
        $this->recursiveScanDir();
        // файл исходных контрольных сумм не скомпроментирован
        if (sizeof($this->version_md5) > 0) {
            foreach ($this->scan_md5 as $found_file => $md5_file) {
                // TODO: добавить чекер на JS
                if ($engine->system->suffixEquals($found_file, '.php') || $engine->system->suffixEquals($found_file, '.phtml') || $engine->system->contains('.php', $found_file)) {
                    if ($this->version_md5[$found_file] == null) {
                        $this->notexist_check_md5[$found_file] = $md5_file;
                    } elseif ($this->version_md5[$found_file] != $md5_file) {
                        $this->wrong_check_md5[$found_file] = $md5_file;
                    } else {
                        $this->success_check_md5[$found_file] = $md5_file;
                    }
                }
            }
        }
        $this->scan_md5 = null;
        $this->version_md5 = null;
    }

    private function loadExcludedDirectory()
    {
        global $engine;
        if(file_exists($engine->constant->root."/cache/.antivir_exclude")) {
            $prepared_file = file_get_contents($engine->constant->root."/cache/.antivir_exclude");
            $this->excluded_directory = $engine->system->altexplode('<=>', $prepared_file);
        }
    }

    private function recursiveScanDir($dir = null)
    {
        global $engine;
        $dir_scan = null;
        if ($dir == null)
            $dir_scan = $engine->constant->root . $engine->constant->ds;
        else
            $dir_scan = $engine->constant->root . $engine->constant->ds . $dir . $engine->constant->ds;
        $objects = scandir($dir_scan);
        foreach ($objects as $item) {
            $md5sum = null;
            if (is_file($dir_scan . $item)) {
                $md5sum = md5_file($dir_scan . $item);
            } elseif (is_dir($dir_scan . $item)) {
                if (!$engine->system->prefixEquals($item, ".")) {
                    if ($dir == null)
                        $this->recursiveScanDir($item);
                    else
                        $this->recursiveScanDir($dir . '/' . $item);
                }
            }
            if ($md5sum != null) {
                $tmp_name = $dir . '/' . $item;
                $is_excluded = false;
                foreach($this->excluded_directory as $excluded_start) {
                    if($engine->system->prefixEquals($tmp_name, substr($excluded_start, 1)))
                        $is_excluded = true;
                }
                if(!$is_excluded)
                    $this->scan_md5[$tmp_name] = $md5sum;
            }
        }
    }

    private function loadVersionMd5List()
    {
        global $engine;
        $md5file = $engine->constant->root . "/resource/antivirus/.md5sum";
        if (file_exists($md5file)) {
            $this->version_md5 = unserialize(file_get_contents($md5file));
        }
    }

    public function containsHackMethods($content)
    {
        if (preg_match('/eval\(|exec\(|passthru\(|shell_exec\(|system\(|proc_open\(|popen,curl_exec\(|curl_multi_exec\(|parse_ini_file\(|show_source\(|base64_/s', $content) && !preg_match('/class antivirus/s', $content)) {
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
        global $engine;
        foreach ($this->notexist_check_md5 as $dir => $md5) {
            $file_root = file_get_contents($engine->constant->root . "/" . $dir);
            if ($this->containsHackMethods($file_root)) {
                $this->hack_check[$dir] = $md5;
            }
        }
        return $this->notexist_check_md5;
    }

    public function getWrongList()
    {
        global $engine;
        foreach ($this->wrong_check_md5 as $dir => $md5) {
            $file_root = file_get_contents($engine->constant->root . "/" . $dir);
            if ($this->containsHackMethods($file_root)) {
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