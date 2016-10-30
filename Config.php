<?php
class Config
{
    protected static $directory;
 
    public static function set_config_directory($directory){
        self::$directory = $directory;
    }
 
    public static function get_config_directory(){
        return rtrim(self::$directory, '/\\');
    }
 
    public static function get($route){
        $values = preg_split('/\./', $route, -1, PREG_SPLIT_NO_EMPTY);
        $key = array_pop($values);
        $file = array_pop($values) . '.php';
        $path = (!empty($values)) ? implode(DIRECTORY_SEPARATOR, $values) . DIRECTORY_SEPARATOR : '';
        $baseDir = self::get_config_directory() . DIRECTORY_SEPARATOR;
        $config = include($baseDir . $path . $file);
        return $config[$key];
    }
}