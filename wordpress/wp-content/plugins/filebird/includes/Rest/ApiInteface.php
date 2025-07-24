<?php
namespace FileBird\Rest;

defined( 'ABSPATH' ) || exit;

interface ApiInterface {
    public function register_rest_routes();
}