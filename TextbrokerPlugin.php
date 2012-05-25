<?php
/**
This file is part of the Textbroker WordPress-Plugin.

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

require_once('PluginStandards.php');
require_once('PluginStandardsInterface.php');
require_once(dirname(__FILE__) . '/textbroker-PHP5-Client/Textbroker.php');
require_once(dirname(__FILE__) . '/textbroker-PHP5-Client/TextbrokerBudgetCheck.php');
require_once(dirname(__FILE__) . '/textbroker-PHP5-Client/TextbrokerBudgetOrder.php');
require_once(dirname(__FILE__) . '/textbroker-PHP5-Client/TextbrokerBudgetOrderChange.php');
require_once(dirname(__FILE__) . '/textbroker-PHP5-Client/TextbrokerBudgetProofreading.php');

/**
 *
 * @package Textbroker WordPress-Plugin
 * @author  Fabio Bacigalupo <info1@open-haus.de>
 * @copyright Fabio Bacigalupo 2012
 * @version $Revision: 2.6 $
 * @since PHP5.2.12
 */
class TextbrokerPlugin extends PluginStandards implements PluginStandardsInterface {

    /**
     * Prefix to differentiate capabilities
     *
     */
    const PREFIX_CAPABILITY         = 'manage_textbrokerplugin_';

    /**
     * Internally used name
     *
     */
    const IDENTIFIER                = 'textbroker';

    /**
     * Option name
     *
     */
    const IDENTIFIER_BUDGETS        = 'textbroker-budgets';

    /**
     * Option name
     *
     */
    const IDENTIFIER_ORDERS         = 'textbroker-orders';

    /**
     * Option name
     *
     */
    const IDENTIFIER_PROOFREADING   = 'textbroker-proofreading';

    /**
     * Name of plugin
     *
     */
    const PLUGIN_NAME               = 'Textbroker-WordPress-Plugin';

    const PARAM_BUDGET              = 'budget';
    const PARAM_BUDGET_ID           = 'budget_id';
    const PARAM_BUDGET_NAME         = 'budget_name';
    const PARAM_BUDGET_ORDER_ID     = 'budget_order_id';
    const PARAM_ORDER               = 'order';
    const PARAM_ORDER_ID            = 'order_id';
    const PARAM_ORDER_COMMENT       = 'order_comment';
    const PARAM_ORDER_RATING        = 'order_review';
    const PARAM_ORDER_TITLE         = 'order_title';
    const PARAM_PUBLISH_TYPE        = 'type';
    const PARAM_COMMENT             = 'comment';
    const PARAM_TEAM_ID             = 'team_id';
    const PARAM_TEAM_NAME           = 'team_name';
    const PARAM_FORCE               = 'force';
    const PARAM_PROOFREADING_ID     = 'proofreading_id_created';
    const PARAM_PROOFREADING_DATE   = 'proofreading_created';
    const PARAM_PROOFREADING_TITLE  = 'proofreading_title';

    const PUBLISH_TYPE_PAGE         = 'page';
    const PUBLISH_TYPE_POST         = 'post';
    const USE_PROOFREAD             = 'use_proofread';

    const ACTION_BUDGET_INFO_ADD    = '1001';
    const ACTION_BUDGET_INFO_INSERT = '1002';
    const ACTION_BUDGET_INFO_EDIT   = '1003';
    const ACTION_BUDGET_INFO_UPDATE = '1004';
    const ACTION_BUDGET_INFO_LIST   = '1005';
    const ACTION_BUDGET_INFO_DELETE = '1006';
    const ACTION_ORDER_ADD          = '2001';
    const ACTION_ORDER_INSERT       = '2002';
    const ACTION_ORDER_GETSTATUS    = '2003';
    const ACTION_ORDER_DELETE       = '2004';
    const ACTION_ORDER_ACCEPT       = '2005';
    const ACTION_ORDER_PREVIEW      = '2006';
    const ACTION_ORDER_REVISE       = '2007';
    const ACTION_ORDER_PUBLISH      = '2008';
    const ACTION_ORDER_REMOVE       = '2009';
    const ACTION_ORDER_REJECT       = '2010';
    const ACTION_ORDER_EDIT         = '2011';
    const ACTION_ORDER_UPDATE       = '2012';
    const ACTION_TEAMORDER_ADD      = '3001';
    const ACTION_TEAMORDER_INSERT   = '3002';
    const ACTION_TEAMORDER_UPDATE   = '3003';
    const ACTION_PROOFREAD_ADD      = '4001';
    const ACTION_PROOFREAD_INSERT   = '4002';
    const ACTION_PROOFREAD_GETSTATUS= '4003';
    const ACTION_PROOFREAD_DELETE   = '4004';
    const ACTION_PROOFREAD_ACCEPT   = '4005';
    const ACTION_PROOFREAD_PREVIEW  = '4006';
    const ACTION_PROOFREAD_REVISE   = '4007';
    const ACTION_PROOFREAD_PICKUP   = '4008';
    const ACTION_PROOFREAD_REJECT   = '4009';

    /**
     * Simple version number
     * Raise this if e.g. stylesheet has changed
     *
     * @var float
     * @see loadScripts()
     */
    const PLUGIN_VERSION            = 2.6;

    /**
     * List of available services
     *
     * @var array
     */
    protected static $aServices     = array(
        #'Check',
        'Order',
    );

    /**
     * List of locations textbroker offer its services
     *
     * @var array
     */
    private static $aLocations      = array(
        'us',
        'de',
        'fr',
        'uk',
        'nl',
        'es',
    );

    /**
     * Singleton
     *
     * @return obj
     */
    public static function &singleton() {

        static $instance;

        if (!isset($instance)) {
            $class      = __CLASS__;
            $instance   = new $class;
        }
        return $instance;
    }

    /**
     * Constructor
     *
     */
    function __construct() {

        parent::__construct();
    }

    /**
     * Load templates and JavaScript files
     *
     */
    public function loadScripts() {

        $stylesheet = $this->getPluginPath() . '/css/' . self::getIdentifier() . '-admin.css';
		wp_enqueue_style(self::getIdentifier() . '-admin', $stylesheet, false, self::getVersion(), 'all');
		parent::loadScripts();
    }

    /**
     * This acts as controller
     *
     */
    public function process() {

        $showBudgetInformation = true;

        try {
            switch ( $_REQUEST['_wpnonce'] ) {
                case wp_create_nonce(self::ACTION_BUDGET_INFO_ADD) :
                    $this->showBudgetInformationEntryForm(array(), false, true);
                    $showBudgetInformation = false;
                    break;
                case wp_create_nonce(self::ACTION_BUDGET_INFO_INSERT) :
                    $this->saveBudget($_REQUEST[self::PARAM_BUDGET]);
                    $this->showMessage(__('Budget information saved successfully', self::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(self::ACTION_BUDGET_INFO_EDIT) :
                    $this->showBudgetInformationEntryForm($this->getBudget($_REQUEST[self::PARAM_BUDGET_ID]), true, true);
                    $showBudgetInformation = false;
                    break;
                case wp_create_nonce(self::ACTION_BUDGET_INFO_UPDATE) :
                    $this->saveBudget($_REQUEST[self::PARAM_BUDGET], true);
                    $this->showMessage(__('Budget information updated successfully', self::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(self::ACTION_BUDGET_INFO_DELETE) :
                    $this->deleteBudget($_REQUEST[self::PARAM_BUDGET_ID]);
                    $this->showMessage(__('Budget information deleted successfully', self::getIdentifier()), self::HINT_SUCCESS, true);
                    break;
                case wp_create_nonce(self::ACTION_BUDGET_INFO_LIST) :
                    $this->showBudgetDetails($this->getBudgetDetails($_REQUEST[self::PARAM_BUDGET_ID]), true);
                    $showBudgetInformation = false;
                    break;
            }
        } catch (TextbrokerBudgetInsertException $e) {
            $this->showMessage(__($e->getMessage(), self::getIdentifier()), self::HINT_ERROR, true);
            $this->showBudgetInformationEntryForm(@$_REQUEST[self::PARAM_BUDGET], false, true);
            $showBudgetInformation = false;
        } catch (TextbrokerBudgetUpdateException $e) {
            $this->showMessage(__($e->getMessage(), self::getIdentifier()), self::HINT_ERROR, true);
            $this->showBudgetInformationEntryForm($_REQUEST[self::PARAM_BUDGET], true, true);
            $showBudgetInformation = false;
        } catch (TextbrokerBudgetException $e) {
            $this->showMessage(__($e->getMessage(), self::getIdentifier()), self::HINT_ERROR, true);
        } catch (Exception $e) {
            $this->showMessage($e->getMessage(), self::HINT_ERROR, true);
        }

        if ($showBudgetInformation) {
            $this->showBudgetInformation($this->getBudgets(), true);
            $this->showTeamInformation($this->getBudgets(), true);
            $this->showBudgetInformationAddButton(true);
        }
    }

    /**
     *
     *
     * @return array
     */
    public function getServices() {

        return self::$aServices;
    }

    /**
     *
     *
     * @return string
     */
    public function getIdentifier() {

        return self::IDENTIFIER;
    }

    /**
     *
     *
     * @return string
     */
    public function getName() {

        return self::PLUGIN_NAME;
    }

    /**
     *
     *
     * @return float
     */
    public function getVersion() {

        return self::PLUGIN_VERSION;
    }

    /**
     *
     *
     * @return string
     */
    public function getCapabilityPrefix() {

        return self::PREFIX_CAPABILITY;
    }

    /**
     *
     *
     * @return array
     */
    public function getLocations() {

        $aLocations = array();
        $aLocations = self::$aLocations;

        if (file_exists(dirname(__FILE__) . '/TextbrokerLocations.php')) {
            require(dirname(__FILE__) . '/TextbrokerLocations.php');
            $aLocations = array_merge($aLocations, TextbrokerLocations::getLocations());
        }

        return $aLocations;
    }

    /**
     *
     *
     * @param int $budgetId
     * @return array
     * @throws TextbrokerBudgetException
     */
    public function getBudget($budgetId) {

        $aBudgets = $this->getOption(self::IDENTIFIER_BUDGETS);

        if(!isset($aBudgets[$budgetId])) {
            throw new TextbrokerBudgetException('ERROR: Failed getting budget');
        }

        return $aBudgets[$budgetId];
    }

/******************************************************************************************************************************************************************************
 * Private processing stuff
 ******************************************************************************************************************************************************************************/

    /**
     * Save the information as an option
     *
     * @param array $aBudget
     * @param bool $isUpdate
     * @throws TextbrokerBudgetInsertException|TextbrokerBudgetUpdateException
     */
    private function saveBudget(array $aBudget, $isUpdate = false) {

        try {
            if (!isset($aBudget['id']) || empty($aBudget['id'])) {
        		throw new Exception("ERROR: Budget id is missing");
            } elseif (!is_numeric($aBudget['id'])) {
        		throw new Exception("ERROR: Budget id is invalid");
            }

            if (!isset($aBudget['key']) || empty($aBudget['key'])) {
        		throw new Exception("ERROR: Budget key is missing");
            }

            if (!isset($aBudget['password']) || empty($aBudget['password'])) {
        		throw new Exception("ERROR: Budget password is missing");
            }

            $aBudget['name']            = TextbrokerBudgetCheck::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location'])->getName();

            if (!$aBudget['name'] || empty($aBudget['name'])) {
        		throw new Exception("ERROR: Budget information is incorrect");
        	}

            $aBudgets                   = $this->getBudgets();
            $aBudgets[$aBudget['id']]   = $aBudget;
            $this->setOption(self::IDENTIFIER_BUDGETS, $aBudgets);
    	} catch (Exception $e) {
        	if ($isUpdate) {
        		throw new TextbrokerBudgetUpdateException($e->getMessage());
        	} else {
        		throw new TextbrokerBudgetInsertException($e->getMessage());
        	}
    	}
    }

    /**
     *
     * @param int $budgetId
     * @throws TextbrokerBudgetException
     */
    private function deleteBudget($budgetId) {

        try {
            $aBudgets                   = $this->getBudgets();
            unset($aBudgets[$budgetId]);
            $this->setOption(self::IDENTIFIER_BUDGETS, $aBudgets);
    	} catch (Exception $e) {
    	    throw new TextbrokerBudgetException($e->getMessage());
    	}
    }

    /**
     *
     *
     * @return array
     */
    public function getBudgets() {

        $aBudgets = $this->getOption(self::IDENTIFIER_BUDGETS);

        if(!is_array($aBudgets)) {
            return array();
        }

        return $aBudgets;
    }

    /**
     *
     *
     * @return array
     */
    private function getTeams($budgetId) {

	    $aBudget                    = $this->getBudget($budgetId);
        $oBudgetOrder               = TextbrokerBudgetOrder::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $aTeams                     = $oBudgetOrder->getTeams();

        if(!is_array($aTeams)) {
            return array();
        }

        return $aTeams;
    }

    /**
     *
     *
     * @param int $budgetId
     * @return array
     */
    private function getBudgetDetails($budgetId) {

	    $aBudget                    = $this->getBudget($budgetId);
        $oBudgetOrder               = TextbrokerBudgetCheck::singleton($aBudget['key'], $aBudget['id'], $aBudget['password'], $aBudget['location']);
        $aUsage                     = $oBudgetOrder->getUsage();

        if (is_array($aUsage) && count($aUsage) > 0) {
        	$aBudget                += $aUsage;
        }

        $aBudget                    += $oBudgetOrder->isInSandbox();
        $aBudget                    += $oBudgetOrder->getActualPeriodData();

        return $aBudget;
    }

    /**
     * Get formatted date
     *
     * @param string $location
     * @param string $timestamp
     * @return string
     */
    protected function getDate($location, $timestamp = null) {

        if (is_null($timestamp)) {
        	$timestamp = time();
        }

        if ($location == 'de') {
        	return date('d.m.Y', $timestamp);
        } else {
        	return date('m/d/Y', $timestamp);
        }
    }

/******************************************************************************************************************************************************************************
 * HTML forms
 ******************************************************************************************************************************************************************************/

    public function getHeader($display = false) {

        parent::getHeader(plugins_url(self::getName() . '/images/logo.gif'), $display);
    }

    /**
     *
     * @param bool $display
     * @return void | string
     */
    public function showBudgetInformationAddButton($display = false) {

        $str    = '
            <p class="submit">
                <a href="%s" class="button right">%s</a>
            </p>
        ';

        $args   = array(
            attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(self::ACTION_BUDGET_INFO_ADD))),
            __('Enter budget information', self::getIdentifier()),
        );

        if ($display) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }

    /**
     *
     * @param bool $display
     * @return void | string
     */
    public function showBudgetInformationEntryForm(array $aBudget = array(), $update = false, $display = false) {

        $str    = '
            <div class="left half">
                <form method="post" action="%s" class="tb">
                    <fieldset>
                        <label for="budget-id">%s</label>
                        <input type="text" name="budget[id]" value="%s" id="budget-id" %s />
                        <label for="budget-key">%s</label>
                        <input type="text" name="budget[key]" value="%s" id="budget-key" %s />
                        <label for="budget-password">%s</label>
                        <input type="password" name="budget[password]" value="%s" id="budget-password" />
                        <label for="budget-location">%s</label>
                        <select name="budget[location]" id="budget-location">
                            %s
                        </select>
                        <p class="submit">
                            <input type="submit" name="do" value="%s" class="button" />
                        </p>
                    </fieldset>
                </form>
            </div>
            <div class="right half">
                <p>
                    %s
                </p>
            </div>
        ';

        $args   = array(
            $update ? attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(self::ACTION_BUDGET_INFO_UPDATE))) : attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(self::ACTION_BUDGET_INFO_INSERT))),
            $update ? __('Budget id', self::getIdentifier()) : __('Enter budget id', self::getIdentifier()),
            @$aBudget['id'],
            $update ? 'readonly="readonly"' : '',
            $update ? __('Budget key', self::getIdentifier()) : __('Enter budget key', self::getIdentifier()),
            @$aBudget['key'],
            $update ? 'readonly="readonly"' : '',
            __('Enter budget password', self::getIdentifier()),
            @$aBudget['password'],
            __('Service location', self::getIdentifier()),
            $this->showLocation(@$aBudget['location']),
            $update ? __('Update budget information', self::getIdentifier()) : __('Save budget information', self::getIdentifier()),
            __('You find your budget information on the Textbroker website.', self::getIdentifier()),
        );

        if ($display) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }

    /**
     *
     *
     * @param array $aBudgets
     * @param bool $display
     * @return string | void
     */
    public function showBudgetInformation(array $aBudgets, $display = false) {

        $str    = '
            <h4>%s</h4>
            <div>
                <ul>
                    %s
                </ul>
            </div>
            <div class="clear"></div>
        ';

        $args   = array(
            __('Budgets', self::getIdentifier()),
            $this->showBudgets($aBudgets),
        );

        if ($display) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }

    /**
     *
     *
     * @param array $aBudgets
     * @param bool $display
     * @return string | void
     */
    public function showTeamInformation(array $aBudgets, $display = false) {

        $str    = '
            <h4>%s</h4>
            <div>
                <ul>
                    %s
                </ul>
            </div>
            <div class="clear"></div>
        ';

        $args   = array(
            __('Teams', self::getIdentifier()),
            $this->showTeams($aBudgets)
        );

        if ($display) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }

    /**
     *
     *
     * @param array $aBudgets
     * @return string
     */
    private function showBudgets(array $aBudgets) {

        $str = null;

        if (count($aBudgets) > 0) {
            foreach ($aBudgets as $aBudget) {
                $str .= sprintf('
                    <li class="clear">
                        <ul class="tb-budgets">
                            <li><img src="%s" alt="%s" /> <a href="%s&%s" title="%s: %s - %s">%s</a></li>
                            <li><a href="%s" class="button">%s</a></li>
                        </ul>
                    </li>
                    ',
                    plugins_url(self::getName() . '/images/' . $aBudget['location'] . '.gif'),
                    __('Service: ' . $aBudget['location'], self::getIdentifier()),
                    attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(self::ACTION_BUDGET_INFO_LIST))),
                    self::PARAM_BUDGET_ID . '=' . $aBudget['id'],
                    __('Budget', self::getIdentifier()),
                    $aBudget['id'],
                    __('List budget details', self::getIdentifier()),
                    $aBudget['name'],
                    get_option('siteurl') . '/wp-admin/admin.php?page=' . self::getName() . '/lib/TextbrokerOrder.php&_wpnonce=' . wp_create_nonce(self::ACTION_ORDER_ADD) . '&' . self::PARAM_BUDGET_ID . '=' . $aBudget['id'] . '&' . self::PARAM_BUDGET_NAME . '=' . $aBudget['name'],
                    __('Add order', self::getIdentifier())
                );
            }
        } else {
            $str = sprintf('<li>%s</li>', __('No budgets', self::getIdentifier()));
        }

        return $str;
    }

    /**
     * Build options for location selector
     *
     * @param string $selected
     * @return string
     */
    private function showLocation($selected) {

        $str = '';

        foreach ($this->getLocations() as $location) {
            $str .= sprintf('<option value="%s" %s>%s</option>', $location, ($location == $selected) ? 'selected="selected"' : '', __('Service: ' . $location, self::getIdentifier()));
        }

        return $str;
    }

    /**
     *
     *
     * @param array $aBudgets
     * @return string
     */
    private function showTeams(array $aBudgets) {

        $str = null;

        if (count($aBudgets) > 0) {
            $aBudget = array_shift($aBudgets);
            $aTeams = $this->getTeams($aBudget['id']);
            $str .= sprintf('
                <li class="clear">
                    <ul class="tb-budgets">
                        <li>
                            <form action="%s" method="post">
                                <input type="hidden" name="%s" value="%s" />
                                <select name="%s">%s</select>
                                <input value="%s" type="submit">
                            </form>
                        </li>
                    </ul>
                </li>
                ',
                get_option('siteurl') . '/wp-admin/admin.php?page=' . self::getName() . '/lib/TextbrokerOrder.php&_wpnonce=' . wp_create_nonce(self::ACTION_TEAMORDER_ADD),
                self::PARAM_BUDGET_ID,
                $aBudget['id'],
                self::PARAM_TEAM_ID,
                $this->buildTeams($aTeams),
                __('Create team order', self::getIdentifier())
            );
        }

        return $str;
    }

    /**
     * Build options for teams
     *
     * @param array $aTeams
     * @return string
     */
    private function buildTeams(array $aTeams) {

        $str = '';

        try {
            foreach ($aTeams as $aTeam) {
                $str .= sprintf('<option value="%s">%s %s %s/%s</option>', $aTeam['team_id'], $aTeam['team_name'], $aTeam['team_price_per_word']/100, __('Local currency', self::getIdentifier()), __('Word', self::getIdentifier()));
            }
        } catch (TextbrokerBudgetOrderException $e) {
            $str .= sprintf('<option value="">%s</option>', __("ERROR: " . $e->getMessage(), self::getIdentifier()));
        }

        return $str;
    }

    /**
     *
     *
     * @param array $aBudget
     * @param bool $display
     * @return string | void
     */
    private function showBudgetDetails(array $aBudget, $display = false) {

        $str = '
            <h4>%s „%s“ <a href="%s&%s" class="button">%s</a></h4>
            <dl class="tb-list">
                <dt>%s:</dt> <dd>%s</dd>
                <dt>%s:</dt> <dd>%s</dd>
                <dt>%s:</dt> <dd>%s</dd>
            </dl>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>%s</th>
                        <th>%s</th>
                        <th>%s</th>
                        <th>%s</th>
                        <th>%s</th>
                        <th>%s</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                    </tr>
                </tbody>
            </table>
            <p class="submit">
                <a href="%s&%s" class="button">%s</a>
            </p>
        ';

        $args   = array(
            __('Budget', self::getIdentifier()),
            $aBudget['name'],
            attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(self::ACTION_BUDGET_INFO_EDIT))), self::PARAM_BUDGET_ID . '=' . $aBudget['id'],
            __('Edit', self::getIdentifier()),
            __('Budget id', self::getIdentifier()),
            $aBudget['id'],
            __('Budget key', self::getIdentifier()),
            $aBudget['key'],
            __('Status', self::getIdentifier()),
            $aBudget['sandbox'] ? __('Sandbox', self::getIdentifier()) : __('Live', self::getIdentifier()),
            __('Period start', self::getIdentifier()),
            __('Period end', self::getIdentifier()),
            __('Budget max', self::getIdentifier()),
            __('Budget left', self::getIdentifier()),
            __('Budget locked', self::getIdentifier()),
            __('Budget used', self::getIdentifier()),
            $this->getDate($aBudget['location'], $aBudget['start']),
            $this->getDate($aBudget['location'], $aBudget['end']),
            $aBudget['max'],
            $aBudget['left'],
            $aBudget['locked'],
            $aBudget['usage'],
            attribute_escape(add_query_arg('_wpnonce', wp_create_nonce(self::ACTION_BUDGET_INFO_DELETE))), self::PARAM_BUDGET_ID . '=' . $aBudget['id'],
            __('Delete', self::getIdentifier()),
        );

        if ($display) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }

    /**
     * Set the name the option list is saved as in WordPress internally
     */
    function setOptionsName() {

        $this->optionsName .= self::IDENTIFIER;
    }
}

/**
 *
 * @package Textbroker WordPress-Plugin
 * @author  Fabio Bacigalupo <info1@open-haus.de>
 * @copyright Fabio Bacigalupo 2010
 * @version $Revision: 1 $
 * @since PHP 5.2
 */
class TextbrokerBudgetException extends Exception {

    public function __construct($message, $code = 0) {

        parent::__construct($message, $code);
    }
}

/**
 *
 * @package Textbroker WordPress-Plugin
 * @author  Fabio Bacigalupo <info1@open-haus.de>
 * @copyright Fabio Bacigalupo 2010
 * @version $Revision: 1 $
 * @since PHP 5.2
 */
class TextbrokerBudgetUpdateException extends TextbrokerBudgetException {

    public function __construct($message, $code = 0) {

        parent::__construct($message, $code);
    }
}

/**
 *
 * @package Textbroker WordPress-Plugin
 * @author  Fabio Bacigalupo <info1@open-haus.de>
 * @copyright Fabio Bacigalupo 2010
 * @version $Revision: 1 $
 * @since PHP 5.2
 */
class TextbrokerBudgetInsertException extends TextbrokerBudgetException {

    public function __construct($message, $code = 0) {

        parent::__construct($message, $code);
    }
}
?>