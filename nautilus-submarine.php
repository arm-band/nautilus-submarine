<?php
/*
Plugin Name: Nautilus Submarine
Description: 投稿者アーカイブを無効化し、WP REST API の users も秘匿する簡易なプラグイン
Version: 0.0.2
Author: アルム＝バンド
License: MIT
*/

// 投稿者アーカイブの無効化
function disable_author_archive() {
    if( isset($_GET['author']) && $_GET['author'] || preg_match('#/author/.+#', $_SERVER['REQUEST_URI']) ){
        wp_redirect( home_url('/404.php') );
        exit;
    }
}
add_action( 'init', 'disable_author_archive' );

// REST API の users を無効化
function deny_rest_api_with_exception( $result, $wp_rest_server, $request ) {
    // oembed, Contact Form 7, Akismet の3つは許可
    $permitted_routes = [ 'oembed', 'contact-form-7', 'akismet'];
    $route = $request->get_route();
    foreach ( $permitted_routes as $r ) {
        if ( strpos( $route, "/$r/" ) === 0 ) {
            return $result;
        }
    }
    // ユーザーが投稿やページの編集が可能な場合にブロックエディタを許可
    if ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' )) {
        return $result;
    }
    return new WP_Error( 'rest_disabled', __( 'The REST API on this site has been disabled.' ), array( 'status' => rest_authorization_required_code() ) );
}
add_filter( 'rest_pre_dispatch', 'deny_rest_api_with_exception', 10, 3 );
