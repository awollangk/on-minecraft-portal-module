<?php
/**
*
* @package Board3 Portal v2 - Whos On Minecraft
* @copyright (c) Alex Wollangk (awollangk@gmail.com)
* @copyright (c) Board3 Group ( www.board3.de )
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
$lang = array_merge($lang, array(
	'WHOS_ON_MINECRAFT' => 'Who\'s On Minecraft',

	// ACP
    'ACP_ON_MINECRAFT_SERVER_LEGEND'     => 'Server Connection Settings',
    'ACP_ON_MINECRAFT_SERVERURL'         => 'Server URL',
    'ACP_ON_MINECRAFT_SERVERURL_EXP'     => 'The address of your server.',
    'ACP_ON_MINECRAFT_SBPORT'            => 'SpaceBukkit Port',
    'ACP_ON_MINECRAFT_SBPORT_EXP'        => 'The SpaceBukkit (NOT RTK) Port of your Minecraft server.',
    'ACP_ON_MINECRAFT_URLKEY'            => 'URL Key',
    'ACP_ON_MINECRAFT_URLKEY_EXP'        => 'The characters "getPlayers" plus your server\'s salt hashed with SHA256 to use as a key when getting the player list from your server.',
    'ACP_ON_MINECRAFT_LIST_LEGEND'       => 'User List Display Settings',
    'ACP_ON_MINECRAFT_SIZE'              => 'Avatar Size',
    'ACP_ON_MINECRAFT_SIZE_EXP'          => 'The size of the avatar in the list next to the player\'s name in pixels.',
    'ACP_ON_MINECRAFT_SERVER_LONELY'     => 'Lonely Server',
    'ACP_ON_MINECRAFT_SERVER_LONELY_EXP' => 'The text to show when nobody is online.',
    'ACP_ON_MINECRAFT_NUM_RECENT'        => 'Recent Users',
    'ACP_ON_MINECRAFT_NUM_RECENT_EXP'    => 'The number of recent users to display.',
    'ACP_ON_MINECRAFT_RECENT_DATE'       => 'Date Format',
    'ACP_ON_MINECRAFT_RECENT_DATE_EXP'   => 'The format to use for the date the recent users most recently logged in.<br/>(See <a href="http://php.net/manual/en/function.date.php">The PHP Date Function</a> for more information.)',
    'ACP_ON_MINECRAFT_SHOW_TOTAL'        => 'Show Total',
    'ACP_ON_MINECRAFT_SHOW_TOTAL_EXP'    => 'Whether to show the total time the recent user has spent on the server.',
    'ACP_ON_MINECRAFT_DB_LEGEND'         => 'Database Settings',
    'ACP_ON_MINECRAFT_TABLE_NAME'        => 'Table Name',
    'ACP_ON_MINECRAFT_TABLE_NAME_EXP'    => 'The database and table name of the table or view containing the last time a user logged in.',
    'ACP_ON_MINECRAFT_PLAYER_FIELD'      => 'Player Name Field',
    'ACP_ON_MINECRAFT_PLAYER_FIELD_EXP'  => 'The name of the field within that table that contains the player\'s Minecraft user name.',
    'ACP_ON_MINECRAFT_LOGIN_FIELD'       => 'Last Login Field',
    'ACP_ON_MINECRAFT_LOGIN_FIELD_EXP'   => 'The name of the field within that table that contains the last date and time the player logged in.',
    'ACP_ON_MINECRAFT_ONLINE_TIME'       => 'Time Online Field',
    'ACP_ON_MINECRAFT_ONLINE_TIME_EXP'   => 'The name of the field within that table that contains the total number of seconds the player has been on the server.',

    // Portal
    'ON_MINECRAFT_NO_SERVER_CONNECTION' => 'Not enough information is provided to be able to connect to the Minecraft server.  The forum administrator must provide all the server connection settings in the "Who\'s On Minecraft" module options to be able to bring up who is logged into the server.',
));
