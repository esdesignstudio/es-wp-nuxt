<?php

namespace FileBird\Controller\Import;

defined( 'ABSPATH' ) || exit;

class DataImport {
    private static $plugins_import = array();

    public $prefix;
    public $name;
    public $author;
    public $taxonomy;
    public $counter;
    public $completed;
    public $noThanks;
    public $description;

    public function __construct( $prefix, $attributes ) {
        $this->prefix = $prefix;

		foreach ( $attributes as $key => $value ) {
            $this->{$key} = $value;
        }

        static::$plugins_import[ $prefix ] = $this;
    }

    public static function get( $prefix = 'all' ) {
        if ( $prefix === 'all' ) {
            return static::$plugins_import;
        } else {
            return static::$plugins_import[ $prefix ];
        }
    }
}

