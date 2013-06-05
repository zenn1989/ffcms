<?php

class Xbb_Tags_Youtube extends bbcode
{
    public $behaviour = 'img';

    function get_html($tree = null)
    {
        $param = htmlspecialchars(parent::get_html($tree));
        return '<iframe width="420" height="315" src="http://www.youtube.com/embed/'.$param.'" frameborder="0" allowfullscreen></iframe>';
    }
}
?>