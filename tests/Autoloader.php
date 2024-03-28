<?php

namespace Webshare;

spl_autoload_register(function ($class) {
    if (str_starts_with($class, __NAMESPACE__ . "\\")) {
        $class = str_replace(__NAMESPACE__ . "\\", "", $class);
    }
    require_once(__DIR__ . "/../function/{$class}.php");
});
