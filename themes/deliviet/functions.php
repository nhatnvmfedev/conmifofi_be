<?php add_action('init', 'handle_preflight');
function handle_preflight() {
    $origin = get_http_origin();
    if ($origin === 'http://localhost:3000') {
        header("Access-Control-Allow-Origin: " . "http://localhost:3000");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Credentials: true");
        header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
        if ('OPTIONS' == $_SERVER['REQUEST_METHOD']) {
            status_header(200);
            exit();
        }
    }
}
add_filter('rest_authentication_errors', 'rest_filter_incoming_connections');
function rest_filter_incoming_connections($errors) {
    $request_server = $_SERVER['REMOTE_ADDR'];
    $origin = get_http_origin();
    if ($origin !== 'http://localhost:3000') return new WP_Error('forbidden_access', $origin, array(
        'status' => 403
    ));
    return $errors;
}

add_action( 'rest_api_init', 'register_user_endpoint' );
function register_user_endpoint() {
    register_rest_route( 'wp/v2', '/register', array(
        'methods' => 'POST',
        'callback' => 'register_user_callback',
    ) );
}

function register_user_callback( $request ) {
    $params = $request->get_params();
    $firstname = $params['firstname'];
    $lastname = $params['lastname'];
    $username = $params['username'];
    $password = $params['password'];
    $email = $params['email'];
    $user_id = wp_create_user( $username, $password, $email );
    if ( is_wp_error( $user_id ) ) {
        return new WP_Error( 'registration_error', $user_id->get_error_message(), array( 'status' => 400 ) );
    } else {
        return array( 'status' => 'success', 'user_id' => $user_id );
    }
}

function remove_post_date_columns($columns) {
  unset($columns['date']);
  unset($columns['comments']);
  unset($columns['tags']);
  return $columns;
}
add_filter('manage_post_posts_columns', 'remove_post_date_columns', 10, 1);

function custom_post_columns($columns) {
  $columns['time_start'] = 'ACF Field Label';
  return $columns;
}
add_filter('manage_post_posts_columns', 'custom_post_columns');

function custom_post_column_data($column, $post_id) {
  if ($column == 'time_start') {
    echo get_field('time_start', $post_id);
  }
}
add_action('manage_post_posts_custom_column', 'custom_post_column_data', 10, 2);

?>