<?php
namespace FileBird\Classes;

class Config {
    private static $loaded_configs = array();

    /**
     * Set config
     *
     * Eg: Config::setConfig('PostType', $data);
     * or: Config::setConfig('PostType.name', 'foo');
     */

    public static function setConfig( $name, $data ) {
        $ex = explode( '.', $name );
        if ( count( $ex ) == 1 ) {
            self::$loaded_configs[ $name ] = $data;
        } elseif ( count( $ex ) == 2 ) {
            if ( ! isset( self::$loaded_configs[ $ex[0] ] ) ) {
                self::$loaded_configs[ $ex[0] ] = array();
            }
            self::$loaded_configs[ $ex[0] ][ $ex[1] ] = $data;
        }

    }

    /**
     * Get loaded config
     *
     * Eg: Config::getConfig('PostType.name');
     */

    public static function getConfig( $name ) {
        $ex = explode( '.', $name );
        if ( count( $ex ) == 2 ) {
            if ( isset( self::$loaded_configs[ $ex[0] ] ) && isset( self::$loaded_configs[ $ex[0] ][ $ex[1] ] ) ) {
                return self::$loaded_configs[ $ex[0] ][ $ex[1] ];
            }
        }
    }
}
