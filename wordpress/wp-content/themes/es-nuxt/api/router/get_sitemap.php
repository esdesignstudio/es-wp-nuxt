<?php
function get_sitemap($request)
{
    // get all post-types
    $post_types = get_post_types(array(
        'public' => true,
    ), 'objects');

    $response = array();

    function get_images_from_post($post_id) {
        $media = get_attached_media('', $post_id);
    
        $image_info = array();
    
        foreach ($media as $attachment) {
            if (get_post_mime_type($attachment->ID) === 'image/jpeg' || get_post_mime_type($attachment->ID) === 'image/png') {
                $image_info[] = array(
                    'title' => $attachment->post_title,
                    'loc' => wp_get_attachment_url($attachment->ID),
                    'caption' => $attachment->post_excerpt,
                    'license' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
                );
            }
        }
    
        return $image_info;
    }

    function get_videos_from_post($post_id) {
        $media = get_attached_media('', $post_id);
    
        $video_info = array();
    
        foreach ($media as $attachment) {
            if (get_post_mime_type($attachment->ID) === 'video/mp4') {
                $video_info[] = array(
                    'title' => $attachment->post_title,
                    'thumbnail_loc' => get_the_post_thumbnail_url($attachment->ID),
                    'description' => $attachment->post_excerpt,
                    'content_loc' => wp_get_attachment_url($attachment->ID),
                    'player_loc' => wp_get_attachment_url($attachment->ID),
                    'duration' => get_post_meta($attachment->ID, '_wp_attachment_metadata', true)['length'],
                    'publication_date' => $attachment->post_date,
                );
            }
        }
    
        return $video_info;
    }

    // Loop through each post type
    foreach ($post_types as $post_type) {
        $post_type_name = $post_type->name;
        if ($post_type_name !== 'attachment' && $post_type_name !== 'blog') {
            $args = array(
                'post_type' => $post_type_name,
                'posts_per_page' => -1,
                'post_status' => 'publish',
            );
    
            $posts = get_posts($args);

            $response = array_merge($response, array_map(function ($post) {
                $images = get_images_from_post($post->ID);
                $videos = get_videos_from_post($post->ID);

                if ($post->post_type === 'post') {
                    $loc = '/blog/' . $post->post_name;
                } else if ($post->post_type === 'page') {
                    $loc = '/' . $post->post_name;
                } else {
                    $loc = '/' . $post->post_type . '/' . $post->post_name;
                }

                return array(
                    'loc' => $loc,
                    'images' => $images,
                    'videos' => $videos,
                );
            }, $posts));
        }
    }

    return new WP_REST_Response($response);
}