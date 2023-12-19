<?php
/**
 * Plugin Name: Simple Voting System
 * Description: Implement a simple voting system with Yes and No buttons.
 * Version: 1.0
 * Author: Teodor Savic
 */

// Enqueue scripts and styles
function enqueue_scripts_and_styles() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('voting-script', plugin_dir_url(__FILE__) . 'js/voting-script.js', array('jquery'), '1.0', true);
    wp_enqueue_style('voting-style', plugin_dir_url(__FILE__) . 'css/voting-style.css');
}
add_action('wp_enqueue_scripts', 'enqueue_scripts_and_styles');

// Add voting section to single post pages
function add_voting_section() {
    if (is_single()) {
        $users_votes = get_post_meta(get_the_ID(), '_users_votes', true);

        if (empty($users_votes) || !is_array($users_votes)) {
            $users_votes = array('yes' => array(), 'no' => array());
        }

        $user_has_voted = in_array(md5(get_user_ip()), $users_votes['yes']) || in_array(md5(get_user_ip()), $users_votes['no']);

        echo '<div id="voting-section" data-post-id="' . get_the_ID() . '">';

        if (!$user_has_voted) {
            echo '<span class="question">WAS THIS ARTICLE HELPFUL?</span> ';
            echo '<button id="vote-yes">Yes</button>';
            echo '<button id="vote-no">No</button>';
        } else {
            echo '<p>You have already voted</p>';
        }

        $yes_votes = count($users_votes['yes']);
        $no_votes = count($users_votes['no']);
        $total_votes = $yes_votes + $no_votes;
        $yes_percentage = ($total_votes > 0) ? ($yes_votes / $total_votes) * 100 : 0;
        $no_percentage = ($total_votes > 0) ? 100 - $yes_percentage : 0;
        ?>

        <div id="voting-results">
            <?php if ($user_has_voted) { ?> <span class="thank-you">THANK YOU FOR YOUR FEEDBACK.</span> <?php } ?>
            <span<?php echo in_array(md5(get_user_ip()), $users_votes['yes']) ? ' class="voted"' : "" ?>>Yes: <?php echo round($yes_percentage, 2) ?>%</span>
            <span<?php echo in_array(md5(get_user_ip()), $users_votes['no']) ? ' class="voted no"' : "" ?>>No: <?php echo round($no_percentage, 2) ?>%</span>
        </div>

        <?php echo '</div>';
    }
}
add_action('wp_footer', 'add_voting_section');

// AJAX handler for voting
function vote_handler() {
    $post_id = $_POST['post_id'];
    $vote_type = $_POST['vote_type'];

    // Get current users' votes
    $users_votes = get_post_meta($post_id, '_users_votes', true);

    if (empty($users_votes) || !is_array($users_votes)) {
        $users_votes = array('yes' => array(), 'no' => array());
    }

    // Get user's IP address using the provided function
    $user_ip = get_user_ip();

    // Check if the user has voted before (using IP address as fingerprint)
    $user_has_voted = in_array(md5($user_ip), $users_votes['yes']) || in_array(md5($user_ip), $users_votes['no']);

    if (!$user_has_voted) {
        // Update users' votes array for the selected vote type
        $users_votes[$vote_type][] = md5($user_ip);

        // Update post meta
        update_post_meta($post_id, '_users_votes', $users_votes);

        // Send back the updated results
        $yes_votes = count($users_votes['yes']);
        $no_votes = count($users_votes['no']);
        $total_votes = $yes_votes + $no_votes;
        $yes_percentage = ($total_votes > 0) ? ($yes_votes / $total_votes) * 100 : 0;
        $no_percentage = 100 - $yes_percentage;

        $response = array(
            'yes_percentage' => round($yes_percentage, 2),
            'no_percentage' => round($no_percentage, 2),
        );

        echo json_encode($response);
    } else {
        // User has already voted
        echo json_encode(array('error' => 'You have already voted.'));
    }

    die();
}

add_action('wp_ajax_vote_action', 'vote_handler');
add_action('wp_ajax_nopriv_vote_action', 'vote_handler');

// Display voting results in post edit screen
function display_voting_results_meta_box() {
    add_meta_box('voting_results', 'Voting Results', 'voting_results_meta_box_content', 'post', 'normal', 'high');
}
add_action('add_meta_boxes', 'display_voting_results_meta_box');

function voting_results_meta_box_content($post) {
    $users_votes = get_post_meta($post->ID, '_users_votes', true);
    $yes_votes = isset($users_votes['yes']) ? count($users_votes['yes']) : 0;
    $no_votes = isset($users_votes['no']) ? count($users_votes['no']) : 0;

    echo '<p>Yes Votes: ' . $yes_votes . '</p>';
    echo '<p>No Votes: ' . $no_votes . '</p>';
}

// Function to get user's IP address
function get_user_ip() {
    $ip_address = '';

    if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
        $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }
    $client = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : ''; //check ip from share internet
    $forward = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : ''; //to check ip is passed from proxy

    $remote = $_SERVER['REMOTE_ADDR'];

    if (!empty($client) && filter_var($client, FILTER_VALIDATE_IP)) {
        $ip_address = $client;
    } elseif (!empty($forward) && filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip_address = $forward;
    } else {
        $ip_address = $remote;
    }
    return $ip_address;
}

// Localize script for ajaxurl
function localize_ajaxurl() {
    ?>
    <script type="text/javascript">
        var frontendajax = {"ajaxurl": "<?php echo admin_url('admin-ajax.php'); ?>"};
    </script>
    <?php
}
add_action('wp_footer', 'localize_ajaxurl');