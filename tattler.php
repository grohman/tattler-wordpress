<?php
/*
Plugin Name: Tattler
Plugin URI: https://github.com/grohman
Description: Wordpress Tattler client.
Version:1.0
Author: Daniel Podrabinek <dpodrabinek@oktopost.com>
Author URI: https://github.com/grohman
*/

require('vendor/autoload.php');

use Tattler\Base\Channels\IUser;
use Tattler\Base\Modules\ITattler;
use Tattler\Common;
use Tattler\Objects\TattlerConfig;

$tattler = null;

add_action('init', 'myStartSession', 1);
add_action('wp_logout', 'myEndSession');
add_action('wp_login', 'myEndSession');

function myStartSession()
{
    if (!session_id()) {
        session_start();
    }
}

function myEndSession()
{
    session_destroy();
}

function getTattler()
{
    $tattlerConfig = get_option('tattler');

    if (isset($tattlerConfig['server'])) {
        $config = new TattlerConfig();
        $config->Server = $tattlerConfig['server'];
        $config->Secure = $tattlerConfig['secure'];
        $config->Namespace = $tattlerConfig['login'];

        /** @var ITattler $tattler */
        $tattler = Common::skeleton(ITattler::class);
        $tattler->setConfig($config);
        return $tattler;
    }
    return false;

}

function tattler_add_menu()
{
    add_options_page('Tattler', 'Tattler', 8, 'tattler', 'tattler_options_page');

}

function tattler_options_page()
{
    if ($_POST['tattler_hidden'] == 'Y') {
        $data = [
            'server' => $_POST['server'],
            'secure' => $_POST['secure'] ? true : false,
            'login'  => $_POST['login'],
        ];
        update_option('tattler', $data);
        echo '<div class="updated"><p><strong>Options saved</strong></p></div>';
    }
    $settings = get_option('tattler');
    include("views" . DIRECTORY_SEPARATOR . "settings.php");
}

function wptuts_scripts()
{
    wp_register_script('sio', 'https://cdn.socket.io/socket.io-1.4.5.js');
    wp_register_script('tattler', plugins_url('/vendor/oktopost/tattler-php/js/tattler.min.js', __FILE__));
    wp_register_script('tattler-wp', plugins_url('/views/tattler-wp.js', __FILE__));

    wp_enqueue_script('sio');
    wp_enqueue_script('tattler');
    wp_enqueue_script('tattler-wp');
}



function example_tattler_query_vars($vars)
{
    $vars[] = 'tattler';
    return $vars;
}


function example_tattler_add_rewrite_rules()
{
    add_rewrite_tag('%tattler%', '([^&]+)');
    add_rewrite_rule(
        'ws/?$',
        'index.php?tattler=ws',
        'top'
    );
}


function example_tattler_template_include()
{
    global $wp_query;

    /** @var ITattler $tattler */
    $tattler = getTattler();

    if (array_key_exists('tattler', $wp_query->query_vars)) {
        header('Content-Type: application/json');
        switch ($wp_query->query_vars['tattler']) {
            case 'ws':
                echo json_encode(['ws' => $tattler->getWsAddress()]);
                break;
            case 'channels':
                $socketId = $_GET['socketId'];

                /** @var IUser $user */
                $user = Common::skeleton(IUser::class);
                $user->setName(session_id())->setSocketId($socketId);

                $tattler->setUser($user);

                echo json_encode(['channels' => $tattler->getChannels()]);
                break;
        }
        die;
    }

    return get_page_template();
}

add_action('admin_menu', 'tattler_add_menu');
add_action('wp_enqueue_scripts', 'wptuts_scripts');
add_filter('query_vars', 'example_tattler_query_vars');
add_action('init', 'example_tattler_add_rewrite_rules');
add_filter('template_include', 'example_tattler_template_include');