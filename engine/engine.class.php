<?php

/**
 * Class engine allow access via magic methods for system engine class
 * Provide allias to all global class
 */
class engine
{
    private $store = array();

    public function __set($object, $value) {
        $this->store[$object] = $value;
    }

    public function __get($object) {
        if(array_key_exists($object, $this->store))
            return $this->store[$object];
        else
            exit("API exception: class [$object] not founded. Called from class: [".xdebug_call_class()."] in method: [".xdebug_call_function()."] in file: [".xdebug_call_file()."]");
    }

    public function engine()
    {
        global $constant, $system, $language, $framework, $database, $event, $extension,
        $page, $user, $template, $cache, $file, $hook, $mail, $admin, $meta, $robot, $rule,
        $antivirus, $backup, $api;

        $this->constant = $constant;
        $this->system = $system;
        $this->language = $language;
        $this->framework = $framework;
        $this->database = $database;
        $this->event = $event;
        $this->extension = $extension;
        $this->page = $page;
        $this->user = $user;
        $this->template = $template;
        $this->cache = $cache;
        $this->file = $file;
        $this->hook = $hook;
        $this->mail = $mail;
        $this->admin = $admin;
        $this->meta = $meta;
        $this->robot = $robot;
        $this->rule = $rule;
        $this->antivirus = $antivirus;
        $this->backup = $backup;
        $this->api = $api;
    }
}