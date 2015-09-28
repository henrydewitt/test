<?php
spl_autoload_register(function ($name) {
    $name=__DIR__.'\\'.$name;

    if (file_exists($name . '.php')) {
        include $name . '.php';
        return true;
    }
    throw new Exception("Unable to load $name.");
});
