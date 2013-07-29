<?php
define('root', $_SERVER['DOCUMENT_ROOT']);
$md5_array = array();
function recursiveScanDir($dir = null)
{
    global $md5_array;
    $dir_scan = null;
    if ($dir == null)
        $dir_scan = root . '/';
    else
        $dir_scan = root . '/' . $dir . '/';
    $objects = scandir($dir_scan);
    foreach ($objects as $item) {
        $md5sum = null;
        if (is_file($dir_scan . $item)) {
            $md5sum = md5_file($dir_scan . $item);
        } elseif (is_dir($dir_scan . $item)) {
            if (!prefixEquals($item, ".")) {
                if ($dir == null)
                    recursiveScanDir($item);
                else
                    recursiveScanDir($dir . '/' . $item);
            }
        }
        if ($md5sum != null)
            $md5_array[$dir . '/' . $item] = $md5sum;
    }
}

function prefixEquals($where, $prefix)
{
    if (strlen($prefix) < 1)
        return false;
    $pharse_prefix = substr($where, 0, strlen($prefix));
    return $pharse_prefix == $prefix ? true : false;
}

recursiveScanDir();
file_put_contents(root . '/resource/antivirus/.md5sum', serialize($md5_array));




?>