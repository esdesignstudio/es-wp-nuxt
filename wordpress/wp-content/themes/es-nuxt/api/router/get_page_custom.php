<?php
function get_page_custom($request)
{
    $parameters = $request->get_params();
    $slug = isset($parameters['slug']) ? sanitize_title($parameters['slug']) : null;

    // 如果不阻擋會拿下一筆資料
    if (!$slug) {
        return new WP_Error(
            'missing_slug',
            'slug is required',
            array('status' => 400)
        );
    }

    $pages = get_posts(array(
        'name'           => $slug,
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
    ));

    $post   = !empty($pages) ? $pages[0] : null;
    $postId = $post ? $post->ID : null;
    $fields = $postId ? get_fields($postId) : null;

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
