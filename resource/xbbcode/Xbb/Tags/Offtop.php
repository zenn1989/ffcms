<?php
class Xbb_Tags_Offtop extends bbcode {
    public $behaviour = 'span';
    function get_html($tree = null) {
        return '<small>'.parent::get_html().'</small>';
    }
}
?>