<?php

namespace engine;

class dumper extends singleton {

    protected static $instance = null;

    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Create full copy of www dir and mysql database
     */
    public function make()
    {
        system::getInstance()->createPrivateDirectory(root . '/backup/');
        $file_mainname = system::getInstance()->toDate(time(), 'd')."_backup";
        $this->zipCreate(root, root . "/backup/".$file_mainname . "_www.zip");
        $this->mysqlDump("/backup/".$file_mainname . "_sql.sql.gz");
    }
    /**
     * Authors: http://stackoverflow.com/questions/1334613/how-to-recursively-zip-a-directory-in-php thx for that
     * @param $source
     * @param $destination
     * @param bool $include_dir
     * @param array $additionalIgnoreFiles
     * @return bool
     */
    private function zipCreate($source, $destination, $include_dir = false, $additionalIgnoreFiles = array())
    {
        // Ignore "." and ".." folders by default
        $defaultIgnoreFiles = array('.', '..');

        // include more files to ignore
        $ignoreFiles = array_merge($defaultIgnoreFiles, $additionalIgnoreFiles);

        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        if (file_exists($destination)) {
            unlink($destination);
        }

        $zip = new \ZipArchive();
        if (!$zip->open($destination, \ZIPARCHIVE::CREATE)) {
            return false;
        }
        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source), \RecursiveIteratorIterator::SELF_FIRST);

            if ($include_dir) {

                $arr = explode("/", $source);
                $maindir = $arr[count($arr) - 1];

                $source = "";
                for ($i = 0; $i < count($arr) - 1; $i++) {
                    $source .= '/' . $arr[$i];
                }

                $source = substr($source, 1);

                $zip->addEmptyDir($maindir);

            }

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // purposely ignore files that are irrelevant
                if (in_array(substr($file, strrpos($file, '/') + 1), $ignoreFiles))
                    continue;

                $file = realpath($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else if (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        $zip->close();
        return true;
    }

    private function mysqlDump($dumpname)
    {
        require_once(root . "/resource/phpmysqldumper/MySQLDump.php");
        $dumper = new \MySQLDump(new \mysqli(
            property::getInstance()->get('db_host'),
            property::getInstance()->get('db_user'),
            property::getInstance()->get('db_pass'),
            property::getInstance()->get('db_name')
            )
        );
        $dumper->save(root . $dumpname);
    }
}







?>