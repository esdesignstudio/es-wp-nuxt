<?php
function get_page_custom($request)
{
    $parameters = $request->get_params();
    $postId = isset($parameters['id']) ? (int)$parameters['id'] : null;

    $post   = get_post($postId);
    $fields = get_fields($postId);


    if ($post && $fields) {
        $fields['post'] = (array) $post;

        $response['data'] = $fields;

        return new WP_REST_Response($response);

    } else {
        return new WP_Error(
            'no_page',
            'No page found',
            array('status' => 404)
        );
    }

}
