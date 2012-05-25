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

/**
 *
 * @package open haus WordPress Plugin
 * @author  Fabio Bacigalupo <info1@open-haus.de>
 * @copyright Fabio Bacigalupo 2011
 * @version $Revision: 2.0 $
 * @since PHP5.2.12
 */
abstract class PluginStandards {

    const HINT_SUCCESS              = 2;
    const HINT_NOTICE               = 1;
    const HINT_WARNING              = 0;
    const HINT_ERROR                = -1;

    /**
     * Holds the path to this class
     *
     * @var string
     */
    protected $pluginPath;

    protected $optionsName            = 'options_';

    /**
     * Constructor
     *
     */
    function __construct() {

        $this->setOptionsName();
        $this->setPluginPath();
        $this->setCaps();
        add_action('init', array($this, 'loadTranslations'));
        add_action('admin_enqueue_scripts', array($this, 'loadScripts'));
    }

    /**
     * Load templates and JavaScript files
     *
     */
    public function loadScripts() {

        $stylesheet = $this->getPluginPath() . '/css/oh-plugin-default.css';
		wp_enqueue_style('oh-plugin-default', $stylesheet, false, $this->getVersion(), 'all');
    }

    /**
     * Load translations through internal translation system
     *
     */
    public function loadTranslations() {

        load_plugin_textdomain($this->getIdentifier(), false, $this->getName() . '/languages');
        load_plugin_textdomain('openhaus', false, $this->getName() . '/languages');
    }

    /**
     *
     *
     * @param bool $display
     * @return void | string
     */
    public function getHeader($logo = null, $display = false) {

        $str    = '
            <div class="wrap">
                <div id="oh-plugin-container">
                    <div id="oh-plugin-content">
                        <div id="oh-plugin-header">
                            <img src="%s" id="oh-plugin-logo" />
                            <h2>%s</h2>
                        </div>
        ';

        $args   = array(
            $logo,
            __($this->getName(), $this->getIdentifier()),
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
     * @return void | bool
     */
    public function getFooter($display = false) {

        $str    = '
                        <p id="oh-plugin-footer">
                            %s
                        </p>
                    </div>
                </div>
            </div>
        ';
        $args   = array(
            __('This plugin is developed by open haus.', 'openhaus'),
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
     * @param string $msg
     * @param string $type
     * @param bool $display
     * @return string | void
     */
    public function showMessage($msg, $type, $display = false) {

        $str    = '
            <div id="message" class="%s fade%s">
                <p>
                    <strong>%s</strong>
                </p>
            </div>
        ';
        $fade   = null;

        switch ( $type ) {
            case self::HINT_WARNING :
                $class  = 'error';
            break;
            case self::HINT_ERROR :
                $class  = 'error';
                $fade   = '-ff0000';
            break;
            case self::HINT_SUCCESS :
            case self::HINT_NOTICE :
            default :
                $class  = 'updated';
            break;
        }

        $args   = array(
            $class,
            $fade,
            $msg,
        );

        if ( $display ) {
            vprintf($str, $args);
        } else {
            return vsprintf($str, $args);
        }
    }

    /**
     *
     *
     * @return string
     */
    protected function getPluginPath() {

        if ( !$this->pluginPath ) {
            $this->setPluginPath();
        }

        return $this->pluginPath;
    }

    /**
     *
     *
     * @return string
     */
    protected function setPluginPath() {

        $this->pluginPath = WP_PLUGIN_URL . DIRECTORY_SEPARATOR . basename(dirname(__FILE__));
    }

    /**
     * Get an option
     *
     * @param string $option
     * @return string
     */
    protected function getOption($option) {

    	$options = $this->getOptions();

    	if ( isset($options[$option]) ) {
    		return $options[$option];
    	}

    	return null;
    }

    /**
     * Set an option
     *
     * @param string $option
     * @param mixed $value
     */
    protected function setOption($option, $value) {

    	$options           = $this->getOptions();
    	$options[$option]  = $value;
    	$this->setOptions($options);
    }

    /**
     * Delete an option
     *
     * @param string $option
     */
    protected function deleteOption($option) {

    	$options = $this->getOptions();
        unset($options[$option]);
        $this->setOptions($options);
    }

    /**
     * Get an option from storage
     *
     * @return mixed
     */
    private function getOptions() {

    	return get_option($this->optionsName);
    }

    /**
     * Save to database through internal system
     *
     * @param array $options
     */
    private function setOptions($options) {

        return update_option($this->optionsName, $options);
    }

    /**
     * Set the name the option list is saved as in WordPress internally
     */
    abstract function setOptionsName();

    abstract function getCapabilityPrefix();

    /**
     * Give administrator the proper rights
     * to access active services
     *
     * @return bool
     */
    private function setCaps() {

        // Set 'manage_PROJECTNAME' Capabilities To Administrator
        $role = get_role('administrator');

        foreach ( $this->getServices() as $service ) {
            $capability = $this->getCapabilityPrefix() . $service;

            if ( $role->has_cap($capability) ) {
            	$role->remove_cap($capability);
            }
        }

        foreach ( $this->getServices() as $service ) {
            $capability = $this->getCapabilityPrefix() . $service;

            if ( !$role->has_cap($capability) ) {
            	$role->add_cap($capability);
            }
        }

        return true;
    }
}
?>