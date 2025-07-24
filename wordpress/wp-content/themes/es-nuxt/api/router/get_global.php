<?php
function get_global($request)
{
    try {
        $response['status'] = 200;
        $response['data'] = get_fields('option');

        return new WP_REST_Response(
            rest_ensure_response($response), 
            $response['status']
        );
    } catch (Exception $e) {
        return new WP_Error(
            'no_global', 
            $e->getMessage(), 
            array('status' => $e->getCode())
        );
    }
}
