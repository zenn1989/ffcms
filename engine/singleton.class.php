<?php
namespace engine;

abstract class singleton {
    protected final function __construct() {}
    protected final function __clone() {}
    protected final function __wakeup() {}
}