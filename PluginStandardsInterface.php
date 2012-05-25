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
 * @author Fabio Bacigalupo <info1@open-haus.de>
 * @copyright Fabio Bacigalupo 2011
 * @version $Revision: 2.0 $
 * @since PHP5.2.12
 */
interface PluginStandardsInterface {

    /**
     * This acts as controller
     *
     */
    public function process();

    /**
     * Returns internally used short version of plugin name
     * used as translation identifier
     *
     */
    function getIdentifier();

    /**
     * Returns plugin name
     *
     */
    function getName();

    /**
     * Returns version information
     *
     */
    function getVersion();
}
?>