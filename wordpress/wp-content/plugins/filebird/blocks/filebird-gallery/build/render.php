<?php

defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( empty( $attributes['selectedFolder'] ) ) {
    return '';
}

$where_arr   = array( '1 = 1' );
$ids         = array_map(function($item) {return intval($item);},$attributes['selectedFolder'] );
$where_arr[] = '`folder_id` IN (' . implode( ',', $ids ) . ')';
$in_not_in   = $wpdb->get_col( "SELECT `attachment_id` FROM {$wpdb->prefix}fbv_attachment_folder" . ' WHERE ' . implode( ' AND ', apply_filters( 'fbv_in_not_in_where_query', $where_arr, $ids ) ) );

if ( empty( $in_not_in ) ) {
    return '';
}

$query = new \WP_Query(
    array(
        'post_type'      => 'attachment',
        'posts_per_page' => -1,
        'post__in'       => $in_not_in,
        'orderby'        => sanitize_text_field( $attributes['sortBy'] ),
        'order'          => sanitize_text_field( $attributes['sortType'] ),
        'post_status'    => 'inherit',
    )
);
$posts = $query->get_posts();
if ( $attributes['sortBy'] == 'file_name' ) {
    if ( $attributes['sortType'] == 'ASC' ) {
        usort(
            $posts,
            function( $img1, $img2 ) {
                return ( basename( $img1->guid ) > basename( $img2->guid ) ) ? 1 : -1;
            }
        );
    } else {
        usort(
            $posts,
            function( $img1, $img2 ) {
                return ( basename( $img1->guid ) > basename( $img2->guid ) ) ? -1 : 1;
            }
        );
    }
}

$ulClass = 'filebird-block-filebird-gallery';

if ( 'flex' === $attributes['layout'] ) {
    $ulClass .= ' wp-block-gallery blocks-gallery-grid';
} elseif ( 'grid' === $attributes['layout'] ) {
    $ulClass .= ' layout-grid';
} elseif ( 'masonry' === $attributes['layout'] ) {
    $ulClass .= ' layout-masonry';
}

$ulClass .= ! empty( $attributes['className'] ) ? ' ' . esc_attr( $attributes['className'] ) : '';
$ulClass .= ' columns-' . esc_attr( $attributes['columns'] );
$ulClass .= $attributes['isCropped'] ? ' is-cropped' : '';

if ( count( $posts ) < 1 ) {
    return '';
}

$styles  = '--columns: ' . esc_attr( $attributes['columns'] ) . ';';
$styles .= '--space: ' . esc_attr( $attributes['spaceAroundImage'] ) . 'px;';
$styles .= '--min-width: ' . esc_attr( $attributes['imgMinWidth'] ) . 'px;';

$html  = '';
$html .= '<ul class="' . esc_attr( $ulClass ) . '" style="' . $styles . '">';

$lis = array();
foreach ( $posts as $post ) {
    if ( ! wp_attachment_is_image( $post ) ) {
        continue;
    }
    $href     = '';
    $imageSrc = wp_get_attachment_image_src( $post->ID, 'full' );
    $imageSrc = $imageSrc[0];
    $imageAlt = get_post_meta( $post->ID, '_wp_attachment_image_alt', true );
    $imageAlt = empty( $imageAlt ) ? $post->post_title : $imageAlt;
    switch ( $attributes['linkTo'] ) {
        case 'media':
            $href = $imageSrc;
            break;
        case 'attachment':
            $href = get_attachment_link( $post->ID );

            break;
        default:
            break;
    }

    $img  = '<img src="' . esc_attr( $imageSrc ) . '" alt="' . esc_html( $imageAlt ) . '"';
    $img .= ' class="' . "wp-image-{$post->ID}" . '"/>';

    $li  = '<li class="blocks-gallery-item">';
    $li .= '<figure>';

    $li .= empty( $href ) ? $img : '<a href="' . esc_attr( $href ) . '">' . $img . '</a>';

    if ( $attributes['hasCaption'] ) {
        $li .= empty( $post->post_excerpt ) ? '' : '<figcaption class="blocks-gallery-item__caption">' . wp_kses_post( $post->post_excerpt ) . '</figcaption>';
    }

    $li .= '</figure>';
    $li .= '</li>';

    $lis[] = $li;
}
if(count($lis) == 0) {
    $html .= '<li><div class="components-notice is-error"><div class="components-notice__content"><p>'.__('This folder has no images, please choose another one.', 'filebird').'</p></div></div></li>';
} else {
    $html .= implode( '', $lis );
}
$html .= '</ul>';

if ( $attributes['hasLightbox'] ) {
    wp_enqueue_style( 'fbv-photoswipe' );
    wp_enqueue_style( 'fbv-photoswipe-default-skin' );

    wp_enqueue_script( 'fbv-photoswipe' );
    wp_enqueue_script( 'fbv-photoswipe-ui-default' );
    wp_enqueue_script( 'filebird-gallery' );
}

echo $html;