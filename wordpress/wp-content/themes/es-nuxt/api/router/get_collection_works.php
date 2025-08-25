<?php
function get_collection_works($request)
{
    $parameters = $request->get_params();
    $id = isset($parameters['id']) ? $parameters['id'] : null;
    $response['status'] = 404;

    if ($id !== 'works_options') {
        $post = get_post($id);

        if ($post) {
            $postID = $post->ID;
            // $postID = pll_get_post($requestID, $parameters['locale']);

            $fields = get_fields($postID) ?: [];
            $fields['post'] = get_post($postID);
            
            $response = $fields;
            $response['status'] = 200;
        }
    } else {
        $response = get_fields('works_options') ?: [];
        $response['status'] = 200;
    }

    return new WP_REST_Response($response);
}
