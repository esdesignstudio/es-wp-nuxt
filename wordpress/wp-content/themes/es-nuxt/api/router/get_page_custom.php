<?php
function get_page_custom($request)
{
    $parameters = $request->get_params();
    $postId = isset($parameters['id']) ? (int)$parameters['id'] : null;

    $post   = get_post($postId);
    $fields = get_fields($postId);


    if ($post && $fields) {
        $fields['post'] = (array) $post;

        if ($post-> ID === 2) {

            // 重新組裝 service
            if (isset($fields['news']['features']) && is_array($fields['news']['features'])) {
                foreach ($fields['news']['features'] as &$item) {
                    $item->category = get_the_terms($item->ID, 'news-category');
                }
            }
        }

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
