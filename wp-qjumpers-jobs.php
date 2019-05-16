<?php
/**
 * Plugin Name: WP Qjumpers Jobs
 * Plugin URI: https://github.com/qjumpersnz/wp-qjumpers-jobs
 * Description: A Wordpress Plugin to embed QJumpers Jobs in your site
 * Version: 0.1.0
 * Author: Andrew Ford
 *
 * @package wp-qjumpers-jobs
 */

if (is_admin()) { // admin actions	
    add_action('admin_menu', 'qj_plugin_menu');
}

function qj_plugin_menu()
{

    //create new settings options page
    add_options_page('QJumpers Jobs Options', 'QJumpers Jobs', 'manage_options', 'wp-qjumpers-jobs', 'qj_plugin_options_page');

    //call register settings function
    add_action('admin_init', 'register_qj_jobs_plugin_settings');
}


function register_qj_jobs_plugin_settings()
{
    //register our settings
    register_setting('qj-jobs-settings-group', 'api_key');
    register_setting('qj-jobs-settings-group', 'api_url');
}

function qj_plugin_options_page()
{
    ?>
    <div class="wrap">
        <h1>QJumpers Jobs Settings</h1>

        <form method="post" action="options.php">
            <?php settings_fields('qj-jobs-settings-group'); ?>
            <?php do_settings_sections('qj-jobs-settings-group'); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="api_key" value="<?php echo esc_attr(get_option('api_key')); ?>" size="30" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">API URL</th>
                    <td><input type="text" name="api_url" value="<?php echo esc_attr(get_option('api_url')); ?>" size="30" /></td>
                </tr>

            </table>

            <?php submit_button(); ?>

        </form>
    </div>
<?php }

// Add your shortcode snippets below.
add_shortcode('qj_jobs', 'qj_jobs_shortcode');

function qj_jobs_shortcode()
{

    $apikey = get_option('api_key');
    $apiurl = get_option('api_url');

    $headers = array(
        'Authorization' => 'Basic ' . $apikey,
        'Accept'        => 'application/json;ver=1.0',
        'Content-Type'  => 'application/json; charset=UTF-8'
    );
    $request = array(
        'headers' => $headers
    );

    // Get data for API call
    $response = wp_remote_get($apiurl, $request);
    try {
        $jsonBody = wp_remote_retrieve_body($response);
        $data = json_decode($jsonBody, true);

        foreach($data['content'] as $obj){
            $address = $obj['address'];
            $link = '//qa.qjumpers.jobs' . '/applications/add/' . $obj['id'] . '?jobinvitationid='
            ?>            
                <div class="qj-jobs">
                    <div class="qj-jobs_row">
                        <div class="qj-jobs_col">
                            <h4><?php echo esc_attr($obj['title']); ?></h4>
                            <span class=""><?php echo esc_attr($obj['industory']); ?></span>
                        </div>
                        <div class="qj-jobs_col">
                            <span class=""><?php echo esc_attr($address['state']); ?> <?php echo esc_attr($address['city']); ?></span>
                        </div>                    
                    </div>
                    <div class="qj-jobs_row qj-jobs_desc">                    
                        <?php echo esc_attr($obj['shortDescription']); ?>                    
                    </div>
                    <div>                
                        <a href="<?php echo esc_attr($link); ?>">Apply</a>
                    </div>
                </div>
            <?php
        }
    } catch (Exception $ex) {
        echo esc_attr("<p>No jobs available</p>");
    } // end try/catch
}
?>