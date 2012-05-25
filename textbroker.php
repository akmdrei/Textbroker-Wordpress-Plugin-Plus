<?php
/*
Plugin Name: Textbroker WordPress-Plugin Modified
Plugin URI: http://www.akm3.de/
Description: This plugin evaluates a hook in the Textbroker Wordpress plugin (http://www.textbroker.de/wordpress/) to automatically highlight the SEO-keywords as bold and push them to All-in-One SEO post_meta entry 
Version: 1.0
Author: Andre Alpar, AKM3
Contributors: AKM3
Requires at least: 3.1
Author URI: http://www.akm3.de/
License: GNU GPL v2


This plugin modification of the Textbroker Wordpress Plugin 2.6 (http://www.textbroker.de/wordpress/) adds hooks to the original plugin
 
Note: This plugin is a fork of the original Textbroker Wordpress Plugin by OpenHaus, so we attach the original license

The Textbroker WordPress-Plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

The Textbroker WordPress Plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with the Textbroker WordPress Plugin.  If not, see http://www.gnu.org/licenses/.
*/  


require_once( 'TextbrokerPlugin.php' );

// Load WP-Config File If This File Is Called Directly
if( !function_exists('add_action') ) {

    $wp_root = '../../..';

    if( file_exists($wp_root.'/wp-load.php') ) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}

if ( class_exists('TextbrokerPlugin') ) {

    // Initialize constructor which loads scripts (CSS, JS)
    TextbrokerPlugin::singleton();
}

/**
 * Load up everything needed to display page
 */
function textbroker_init() {

    $textbroker_plugin = TextbrokerPlugin::singleton();

    if (!class_exists('SoapClient')) {
        die(__("SOAP must be installed", $textbroker_plugin->getIdentifier()));
    }

    if (version_compare(PHP_VERSION, '5.2.0', '<')) {
        die(__("Installed version of PHP is too old", $textbroker_plugin->getIdentifier()));
    }

    $textbroker_plugin->loadScripts();
    $textbroker_plugin->getHeader(true);
    $textbroker_plugin->process();
    $textbroker_plugin->getFooter(true);
}

function textbrokerCheck_init() {

    require_once(dirname(__FILE__) . '/lib/TextbrokerCheck.php');
    $textbroker_check = TextbrokerCheck::singleton();
    $textbroker_check->loadScripts();
    $textbroker_check->getHeader(true);
    $textbroker_check->process();
    $textbroker_check->getFooter(true);
}

function textbrokerOrder_init() {

    if (!class_exists('SoapClient')) {
        die(__("SOAP must be installed", TextbrokerPlugin::singleton()->getIdentifier()));
    }

    require_once(dirname(__FILE__) . '/lib/TextbrokerOrder.php');
    $textbroker_order = TextbrokerOrder::singleton();
    $textbroker_order->loadScripts();
    $textbroker_order->getHeader(true);
    $textbroker_order->process();
    $textbroker_order->getFooter(true);
}

// Function: Add JavaScript stuff
add_action('admin_head-textbroker_page_Textbroker-WordPress-Plugin/lib/TextbrokerOrder', 'display_order_javascript');
add_action('wp_ajax_get_cost_action', 'cost_action_callback');
add_action('wp_ajax_get_proofreadcost_action', 'proofread_action_callback');
// Function: Textbroker Administration Menu
add_action( 'admin_menu', 'textbroker_menu' );
function textbroker_menu() {

    $textbrokerPlugin = TextbrokerPlugin::singleton();

	if( function_exists('add_menu_page') ) {
		add_menu_page(__('Textbroker', $textbrokerPlugin->getIdentifier()), __('Textbroker', $textbrokerPlugin->getIdentifier()), 'manage_options', $textbrokerPlugin->getName() . '/textbroker.php', 'textbroker_init', plugins_url($textbrokerPlugin->getName() . '/images/textbroker_icon.png'), 8);
	}

	if( function_exists('add_submenu_page') ) {
	    add_submenu_page($textbrokerPlugin->getName() . '/textbroker.php', __('Textbroker', $textbrokerPlugin->getIdentifier()), __('Manage budgets', $textbrokerPlugin->getIdentifier()), 'manage_options', $textbrokerPlugin->getName() . '/textbroker.php', 'textbroker_init');
	    foreach ( $textbrokerPlugin->getServices() as $service ) {
	        $service       = ucfirst(strtolower($service));
	        $capability    = $textbrokerPlugin->getCapabilityPrefix() . $service;
    	    $parent        = $textbrokerPlugin->getName() . '/textbroker.php';
    	    $page_title    = __('Submenu: ' . $service, $textbrokerPlugin->getIdentifier());
    	    $menu_title    = $page_title;
    	    $file          = $textbrokerPlugin->getName() . '/lib/Textbroker' . $service . '.php';
    	    $init          = null;

    	    if (isset($_REQUEST['page']) && $_REQUEST['page'] == $file) {
    	    	$init = 'textbroker' . $service . '_init';
    	    }

    		add_submenu_page($parent, $page_title, $menu_title, $capability, $file, $init);
	    }
	}
}

function display_order_javascript() {

    $code = <<<END
<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#rating').blur(function() {
        check_cost();
    });
    $('#words-max').blur(function() {
        check_cost();
    });
    $('#order-form').ready(function(){
        check_cost();
    });
    $('#proofread-order-form').ready(function(){
        check_proofread_cost();
    });
    $('#order-text').blur(function(){
        check_cost();
    });
    $('#proofread-text').blur(function(){
        check_proofread_cost();
    });
    $('#keyword-check-1').click(function(){
        $('#keyword-check-details').show();
    });
    $('#keyword-check-0').click(function(){
        $('#keyword-check-details').hide();
    });
    $('#order-title-enable').click(function(){
        $('#order-title-proofread').toggle();
    });
    $('#order-text-enable').click(function(){
        $('#order-text-proofread').toggle();
    });
    function check_cost() {
        if($('#rating').val() && $('#rating').val()>1) {
            var data = {
                action: 'get_cost_action',
                budget_id: $('#budget-id').val(),
                classification: $('#rating').val(),
                words_max: $('#words-max').val()
            };
        } else if($('#team-id').val() && $('#team-id').val().length>0) {
            var data = {
                action: 'get_cost_action',
                budget_id: $('#budget-id').val(),
                team_id: $('#team-id').val(),
                words_max: $('#words-max').val(),
            };
        } else if($('#order-text').val() && $('#order-text').val().length>0) {
            var data = {
                action: 'get_cost_action',
                text: $('#order-text').val()
            };
        }

        if(data) {
        	$.post(ajaxurl, data, function(response) {
                $("#cost_word_count").text(response.word_count);
                $("#cost_per_word").text(response.cost_per_word_formatted);
                $("#cost_order").text(response.cost_order_formatted);
                $("#cost_tb").text(response.cost_order_fee_formatted);
                $("#cost_total").text(response.cost_total_formatted);
        	}, 'json');
        }
    }
    function check_proofread_cost() {
        var data = {
            action: 'get_proofreadcost_action',
            text: $('#proofread-text').val()
        };
    	$.post(ajaxurl, data, function(response) {
            $("#proofread-cost_word_count").text(response.word_count);
            $("#proofread-cost_per_word").text(response.cost_per_word_formatted);
            $("#proofread-cost_total").text(response.cost_total_formatted);
    	}, 'json');
    }
    function word_count(field) {
        var number = 0;
        var matches = $(field).val().match(/\b/g);
        if(matches) {
            number = matches.length/2;
        }

        return number;
    }
});
</script>
END;
    echo $code;
}

function cost_action_callback() {

    if (isset($_POST['budget_id']) && !empty($_POST['budget_id'])) {
        $aBudget = TextbrokerPlugin::singleton()->getBudget($_POST['budget_id']);
        $tbBudgetOrder = new TextbrokerBudgetOrder($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);

        if (isset($_POST['team_id']) && !empty($_POST['team_id'])) {
            $aCost = $tbBudgetOrder->getCostsTeamOrder($_POST['team_id'], $_POST['words_max']);
        } else {
            $aCost = $tbBudgetOrder->getCosts($_POST['words_max'], $_POST['classification']);
        }
    }

    if (isset($aCost) && count($aCost) > 0) {
        $aCost['cost_per_word_formatted'] = $aCost['cost_per_word'] . ' ' . $aCost['currency'];
        $aCost['cost_order_formatted'] = sprintf("%01.2f", $aCost['cost_order']) . ' ' . $aCost['currency'];
        $aCost['cost_order_fee_formatted'] = sprintf("%01.2f", $aCost['cost_order_fee']) . ' ' . $aCost['currency'];
        $aCost['cost_total_formatted'] = sprintf("%01.2f", $aCost['cost_total']) . ' ' . $aCost['currency'];
    	$response = json_encode($aCost);
    	header("Content-Type: application/json");
    	echo $response;
    }
	exit();
}

function proofread_action_callback() {

    if (isset($_POST['text'])) {
        $aBudgets = TextbrokerPlugin::singleton()->getBudgets();
        $aBudget = array_shift($aBudgets);
        $tbBudgetProofreading = new TextbrokerBudgetProofreading($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $aCost = $tbBudgetProofreading->getCosts($_POST['text']);
    }

    if (isset($aCost) && count($aCost) > 0) {
        $aCost['cost_per_word_formatted'] = $aCost['cost_per_word'] . ' ' . $aCost['currency'];
        $aCost['cost_total_formatted'] = sprintf("%01.2f", $aCost['cost_total']) . ' ' . $aCost['currency'];
    	$response = json_encode($aCost);
    	header("Content-Type: application/json");
    	echo $response;
    }
	exit();
}
?>