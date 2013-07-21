<?php
// demo of callback API
// /api.php?action=apicallback&object=hello
class api_hello_front
{
    public function load()
    {
        $string = "Hello world from api calling back";
        return $string;
    }
}
?>