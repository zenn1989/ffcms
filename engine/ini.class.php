<?php
/**
|==========================================================|
|========= @copyright Pyatinskii Mihail, 2013-2014 ========|
|================= @website: www.ffcms.ru =================|
|========= @license: GNU GPL V3, file: license.txt ========|
|==========================================================|
 */

namespace engine;

class ini extends singleton {

    /**
     * Read ini structure data and return as associative array or FALSE if file not founded
     * @param string $file
     * @param bool $sections
     * @return array|bool
     */
    public function read($file, $sections = false) {
        if(!system::getInstance()->prefixEquals($file, root))
            $file = root . $file;
        if(!file_exists($file))
            return false;
        return parse_ini_file($file, $sections);
    }

    /**
     * Write data to ini file using $assoc_arr. Attention! File be total overwrite'd! Before use this function - read data from existing ini file and compare arrays.
     * @param array $assoc_arr
     * @param string $path
     * @param bool $has_sections
     * @return bool
     */
    public function write($assoc_arr, $path, $has_sections=FALSE) {
        $content = "";
        if ($has_sections) {
            foreach ($assoc_arr as $key=>$elem) {
                $content .= "[".$key."]\n";
                foreach ($elem as $key2=>$elem2) {
                    if(is_array($elem2))
                    {
                        for($i=0;$i<count($elem2);$i++)
                        {
                            $content .= $key2."[] = '".addslashes($elem2[$i])."'\n";
                        }
                    }
                    else if($elem2=="") $content .= $key2." = \n";
                    else $content .= $key2." = '".addslashes($elem2)."'\n";
                }
            }
        }
        else {
            foreach ($assoc_arr as $key=>$elem) {
                if(is_array($elem))
                {
                    for($i=0;$i<count($elem);$i++)
                    {
                        $content .= $key."[] = '".addslashes($elem[$i])."'\n";
                    }
                }
                else if($elem=="") $content .= $key." = \n";
                else $content .= $key." = '".addslashes($elem)."'\n";
            }
        }

        if (!$handle = fopen($path, 'w')) {
            return false;
        }
        if (!fwrite($handle, $content)) {
            return false;
        }
        fclose($handle);
        return true;
    }
}