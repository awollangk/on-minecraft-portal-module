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
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package WhosOnMinecraft
*/
class portal_on_minecraft_module
{
	/**
	* Allowed columns: Just sum up your options (Exp: left + right = 10)
	* top		 1
	* left		 2
	* center	 4
	* right		 8
	* bottom	16
	*/
	public $columns = 31;

	/**
	* Default modulename
	*/
	public $name = 'WHOS_ON_MINECRAFT';

	/**
	* Default module-image:
	* file must be in "{T_THEME_PATH}/images/portal/"
	*/
	public $image_src = 'whosonminecraft.png';

	/**
	* module-language file
	* file must be in "language/{$user->lang}/mods/portal/"
	*/
	public $language = 'portal_on_minecraft_module';

	/**
	* custom acp template
	* file must be in "adm/style/portal/"
	*/
	public $custom_acp_tpl = '';

	/**
	* hide module name in ACP configuration page
	*/
	public $hide_name = false;

    /**
     * @var string A cache of the URL to use to access the server
     */
    private $curl_url = '';

    /******************
     * Config Methods *
     ******************/

    /**
     * @param  int    $module_id   The ID of the current instance of this module.
     * @param  string $config_name The name of the configuration value.
     * @return mixed  The value of the associated configuration.
     */
    private function get_module_config($module_id, $config_name)
    {
        global $config;
        return $config['board3_on_minecraft_' . $config_name . '_' . $module_id];
    }

    /**
     * @param  int    $module_id The ID of the current instance of this module.
     * @return string The URL used to access the server.
     */
    private function get_server_url($module_id)
    {
        return strval($this->get_module_config($module_id, 'server_url'));
    }

    /**
     * @param  int $module_id The ID of the current instance of this module.
     * @return int The (NON RTK) port SpaceBukkit is configured to respond on.
     */
    private function get_sb_port($module_id)
    {
        return intval($this->get_module_config($module_id, 'sb_port'));
    }

    /**
     * @param  int    $module_id The ID of the current instance of this module.
     * @return string The key used to access SpaceBukkit consisting of
     * "getPlayers" and the server's salt hashed with the SHA256 algorithm.
     */
    private function get_url_key($module_id)
    {
        return strval($this->get_module_config($module_id, 'url_key'));
    }

    /**
     * @see portal_on_minecraft_module::get_server_url()
     * @see portal_on_minecraft_module::get_sb_port()
     * @see portal_on_minecraft_module::get_url_key()
     * @param  int  $module_id The ID of the current instance of this module.
     * @return bool Whether there are enough configuration values to connect to
     * SpaceBukkit and retrieve the list of online players.
     */
    private function get_has_server_connection($module_id)
    {
        $host = $this->get_server_url($module_id);
        $port = $this->get_sb_port($module_id);
        $key  = $this->get_url_key($module_id);
        return !(empty($host) || 0 == $port || empty($key));
    }

    /**
     * @param  int $module_id The ID of the current instance of this module.
     * @return int The size of the player avatar in pixels.  This size can
     * either be zero to disable player avatars or between 8 and 64.
     */
    private function get_avatar_size($module_id)
    {
        $size = intval($this->get_module_config($module_id, 'avatar_size'));
        return (0 == $size) ? 0 : min(64,max(8,$size));
    }

    /**
     * @param  int  $module_id The ID of the current instance of this module.
     * @return bool Whether to show the avatar in the user lists.
     */
    private function get_show_avatar($module_id)
    {
        return 0 < $this->get_avatar_size($module_id);
    }

    /**
     * @param  int    $module_id The ID of the current instance of this module.
     * @return string The text to display when there is nobody logged into the
     * Minecraft server.
     */
    private function get_nobody_on($module_id)
    {
        return strval($this->get_module_config($module_id, 'nobody_on'));
    }

    /**
     * @param  int $module_id The ID of the current instance of this module.
     * @return int How many recent logins to display.  This can either be zero
     * to hide the recent logins or a number up to 100.
     */
    private function get_num_recent($module_id)
    {
        return min(100,max(0,intval($this->get_module_config($module_id, 'num_recent'))));
    }

    /**
     * @see http://php.net/manual/en/function.date.php The PHP Date Function
     * @param  int    $module_id The ID of the current instance of this module.
     * @return string The format to use for the recent date.  This can be an
     * empty string to disable display of the date and time the user logged in.
     */
    private function get_recent_date($module_id)
    {
        return strval($this->get_module_config($module_id, 'recent_date'));
    }

    /**
     * @param  int    $module_id The ID of the current instance of this module.
     * @return string The name of the database and table or view that contains
     * the date and time users last logged on.
     */
    private function get_table_name($module_id)
    {
        return strval($this->get_module_config($module_id, 'table_name'));
    }

    /**
     * @param  int    $module_id The ID of the current instance of this module.
     * @return string The name of the field within the table containing
     * players' last login time that contains the player's Minecraft user name.
     */
    private function get_player_field($module_id)
    {
        return strval($this->get_module_config($module_id, 'player_field'));
    }

    /**
     * @param  int    $module_id The ID of the current instance of this module.
     * @return string The name of the field within the table containing
     * players' last login time that contains the date and time the player last
     * logged on.
     */
    private function get_login_field($module_id)
    {
        return strval($this->get_module_config($module_id, 'login_field'));
    }

    /**
     * @param  int    $module_id The ID of the current instance of this module.
     * @return string The name of the field within the table containing
     * players' last login time that contains the total amount of time the
     * player has spent on the server.  If this value is an empty string the
     * total amount of time online will not be displayed.
     */
    private function get_online_time($module_id)
    {
        return strval($this->get_module_config($module_id, 'online_time'));
    }

    /**
     * @see portal_on_minecraft_module::get_num_recent()
     * @see portal_on_minecraft_module::get_table_name()
     * @see portal_on_minecraft_module::get_player_field()
     * @see portal_on_minecraft_module::get_login_field()
     * @param  int  $module_id The ID of the current instance of this module.
     * @return bool Whether to show recent logins.  Recent logins will be shown
     * if there is a positive, non-zero number of recent logins to show, a
     * table name, player name field, and login field configured.
     */
    private function get_show_recent($module_id)
    {
        $num    = $this->get_num_recent($module_id);
        $table  = $this->get_table_name($module_id);
        $player = $this->get_player_field($module_id);
        $login  = $this->get_login_field($module_id);

        return !(0 == $num || empty($table) || empty($player) || empty($login));
    }

    /**
     * @param  int  $module_id The ID of the current instance of this module.
     * @return bool Whether to show the total time the user has been on the
     * server.
     */
    private function get_show_total($module_id)
    {
        $total = (bool)$this->get_module_config($module_id, 'show_total');
        $field = $this->get_online_time($module_id);
        return $total && !empty($field);
    }

    /******************
     * Helper Methods *
     ******************/

    /**
     * @param  mixed  $module_id The ID of the current instance of this module.
     * @return string The URL of the JSON web service to call to retrieve the
     * list of users logged into the minecraft server.
     */
    private function get_curl_url($module_id)
    {
        // If the curl_url is not set but it can be, set it.
        if( '' == $this->curl_url && $this->get_has_server_connection($module_id) ) {
            $host = $this->get_server_url($module_id);
            $key  = $this->get_url_key($module_id);

            $this->curl_url = sprintf(
                'http://%s:%s/call?method=getPlayers&args=%%5B%%5D&key=%s',
                $this->get_server_url($module_id),
                $this->get_sb_port($module_id),
                $this->get_url_key($module_id)
            );
        }
        // Return the curl URL
        return $this->curl_url;
    }

    /**
     * @param  int   $module_id The ID of the current instance of this module.
     * @return array An array of strings containing the Minecraft user names of
     * the users currently logged into the server.
     */
    private function get_current_minecraft_users($module_id)
    {
        $users = array();

        if( $this->get_has_server_connection($module_id) )
        {
            $c = curl_init($this->get_curl_url($module_id));
            curl_setopt($c, CURLOPT_PORT,           $this->get_sb_port($module_id));
            curl_setopt($c, CURLOPT_HEADER,         false);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, true );
            $users = json_decode(curl_exec($c), true);
            curl_close($c);
        }

        return $users;
    }

    /**
     * @param  int   $module_id  The ID of the current instance of this module.
     * @return array An array of recent logins represented by associative
     * arrays with the player name in the "playername" field, the time that
     * player last logged on in the "lastlogin" field and the total amount of
     * time that player has spent on the server in the "onlinetime" field if
     * that field is configured.
     */
    private function get_recent_minecraft_users($module_id)
    {
        global $db;
        $recent       = array();
        $table_name   = $this->get_table_name($module_id);
        $player_field = $this->get_player_field($module_id);
        $login_field  = $this->get_login_field($module_id);
        $online_time  = $this->get_online_time($module_id);
        $select       = "p.$player_field AS playername, p.$login_field AS lastlogin";
        $order_by     = "p.$login_field DESC";

        if( $this->get_show_total($module_id) )
        {
            $select .= ", p.$online_time AS onlinetime";
        }

        $sql = $db->sql_build_query('SELECT_DISTINCT', array(
            'SELECT'   => $select,
            'FROM'     => array(
                $table_name => 'p',
            ),
            'ORDER_BY' => $order_by,
        ));

        $result = $db->sql_query_limit($sql, $this->get_num_recent($module_id));

        while ($row = $db->sql_fetchrow($result))
        {
            $recent[] = $row;
        }

        return $recent;
    }

    /**
     * @param  int    $num_seconds A number of seconds.
     * @return string A string describing that number of seconds in the format
     * days:hours:minutes:seconds with any leading zero values omitted. For
     * example: $num_seconds of 86400 would return "1:0:0:0", $num_seconds of
     * 3600 would return "1:0:0" and $num_seconds of 39 would simply return
     * "39".
     */
    private function total_time($num_seconds)
    {
        $seconds  = intval($num_seconds);
        $days     = floor($seconds / 86400);
        $seconds -= $days * 86400;
        $hours    = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes  = floor($seconds / 60);
        $seconds -= $minutes * 60;

        return ltrim("$days:$hours:$minutes:$seconds", '0:');
    }

    /**
     * A method to set template variables to avoid copy-pasted code between
     * methods get_template_center and get_template_side.
     *
     * @see portal_on_minecraft_module::get_template_center()
     * @see portal_on_minecraft_module::get_template_side()
     * @param int $module_id The ID of the current instance of this module.
     */
    private function set_template_vars($module_id)
    {
        global $template;

        $current    = $this->get_current_minecraft_users($module_id);
        $size       = $this->get_avatar_size($module_id);
        $date_fmt   = $this->get_recent_date($module_id);
        $recent     = $this->get_show_recent($module_id)
                      ? $this->get_recent_minecraft_users($module_id)
                      : array();

        foreach( $current as $user )
        {
            $template->assign_block_vars('b3p_on_minecraft_current_users', array(
                'USERNAME' => $user,
            ));
        }

        foreach( $recent as $user )
        {
            $args = array(
                'USERNAME' => $user['playername'],
                'TIME'     => empty($date_fmt) ? '' : date( $date_fmt, strtotime( $user['lastlogin'] ) ),
            );
            if( $this->get_show_total($module_id) )
            {
                $args['TOTAL'] = $this->total_time($user['onlinetime']);
            }
            $template->assign_block_vars('b3p_on_minecraft_recent_users', $args);
        }

        $template->assign_vars(array(
            'HAS_CONNECTION' => $this->get_has_server_connection($module_id),
            'SHOW_AVATAR'    => $this->get_show_avatar($module_id),
            'AVATAR_SIZE'    => $size,
            'LONELY'         => $this->get_nobody_on($module_id),
            'HAVE_RECENT'    => $this->get_show_recent($module_id),
            'SHOW_TIME'      => !empty($date_fmt),
            'SHOW_TOTAL'     => $this->get_show_total($module_id),
        ));
    }

    /******************
     * Public Methods *
     ******************/

    /**
     * Set up the template when this module is added to the center content
     * area.
     *
     * @see portal_on_minecraft_module::set_template_vars()
     * @param  int    $module_id The ID of the current instance of this module.
     * @return string The file name of the template to use to render the contents
     * of this portal when it is placed in the center content area.
     */
    public function get_template_center($module_id)
	{
        $this->set_template_vars($module_id);
		return 'portal_on_minecraft_center.html';
	}

    /**
     * Set up the template when this module is added to a side content area.
     *
     * @see portal_on_minecraft_module::set_template_vars()
     * @param  int    $module_id The ID of the current instance of this module.
     * @return string The file name of the template to use to render the contents
     * of this portal when it is placed in a side content area.
     */
	public function get_template_side($module_id)
	{
        $this->set_template_vars($module_id);
        return 'portal_on_minecraft_side.html';
	}

    /**
     * Get the template used to populate the settings for this module within
     * the Administrator Control Panel (ACP).
     *
     * @param  int   $module_id The ID of the instance of this module.
     * @return array The array of values defining the configuration settings for this module.
     */
    public function get_template_acp($module_id)
	{
		return array(
			'title'	=> 'WHOS_ON_MINECRAFT',
			'vars'	=> array(
				'legend1'      => 'ACP_ON_MINECRAFT_SERVER_LEGEND',
                'board3_on_minecraft_server_url_'   . $module_id => array(
                    'lang'     => 'ACP_ON_MINECRAFT_SERVERURL',
                    'validate' => 'string',
                    'type'     => 'text:10:255',
                    'explain'  => true),
                'board3_on_minecraft_sb_port_'      . $module_id => array(
                    'lang'     => 'ACP_ON_MINECRAFT_SBPORT',
                    'validate' => 'int',
                    'type'     => 'text:10:6',
                    'explain'  => true),
				'board3_on_minecraft_url_key_'      . $module_id => array(
                    'lang'     => 'ACP_ON_MINECRAFT_URLKEY',
                    'validate' => 'string',
                    'type'     => 'text:20:65',
                    'explain'  => true),
                'legend2'      => 'ACP_ON_MINECRAFT_LIST_LEGEND',
                'board3_on_minecraft_avatar_size_'  . $module_id => array(
                    'lang'     => 'ACP_ON_MINECRAFT_SIZE',
                    'validate' => 'int',
                    'type'     => 'text:10:3',
                    'explain'  => true),
                'board3_on_minecraft_nobody_on_'    . $module_id => array(
                    'lang'     => 'ACP_ON_MINECRAFT_SERVER_LONELY',
                    'validate' => 'string',
                    'type'     => 'text:20:255',
                    'explain'  => true),
                'board3_on_minecraft_num_recent_'   . $module_id => array(
                    'lang'     => 'ACP_ON_MINECRAFT_NUM_RECENT',
                    'validate' => 'string',
                    'type'     => 'text:20:255',
                    'explain'  => true),
                'board3_on_minecraft_recent_date_'  . $module_id => array(
                    'lang'     => 'ACP_ON_MINECRAFT_RECENT_DATE',
                    'validate' => 'string',
                    'type'     => 'text:10:255',
                    'explain'  => true),
                'board3_on_minecraft_show_total_'   . $module_id => array(
                    'lang'     => 'ACP_ON_MINECRAFT_SHOW_TOTAL',
                    'validate' => 'bool',
                    'type'     => 'radio:yes_no',
                    'explain'  => true),
                'legend3'      => 'ACP_ON_MINECRAFT_DB_LEGEND',
                'board3_on_minecraft_table_name_'   . $module_id => array(
                    'lang'     => 'ACP_ON_MINECRAFT_TABLE_NAME',
                    'validate' => 'string',
                    'type'     => 'text:10:255',
                    'explain'  => true),
                'board3_on_minecraft_player_field_' . $module_id => array(
                    'lang'     => 'ACP_ON_MINECRAFT_PLAYER_FIELD',
                    'validate' => 'string',
                    'type'     => 'text:10:255',
                    'explain'  => true),
                'board3_on_minecraft_login_field_'  . $module_id => array(
                    'lang'     => 'ACP_ON_MINECRAFT_LOGIN_FIELD',
                    'validate' => 'string',
                    'type'     => 'text:10:255',
                    'explain'  => true),
                'board3_on_minecraft_online_time_'  . $module_id => array(
                    'lang'     => 'ACP_ON_MINECRAFT_ONLINE_TIME',
                    'validate' => 'string',
                    'type'     => 'text:10:255',
                    'explain'  => true),
            ),
		);
	}

	/*****************
	 * API Functions *
	 *****************/

    /**
     * @param  int  $module_id The ID of the instance of this module.
     * @return bool Whether the module installed correctly.
     */
    public function install($module_id)
	{
        set_config('board3_on_minecraft_server_url_'   . $module_id, 'localhost');
        set_config('board3_on_minecraft_sb_port_'      . $module_id, '2011');
        set_config('board3_on_minecraft_url_key_'      . $module_id, '');
        set_config('board3_on_minecraft_avatar_size_'  . $module_id, '16');
        set_config('board3_on_minecraft_nobody_on_'    . $module_id, 'Nobody Online');
        set_config('board3_on_minecraft_num_recent_'   . $module_id, '10');
        set_config('board3_on_minecraft_recent_date_'  . $module_id, 'n/j g:ia');
        set_config('board3_on_minecraft_show_total_'   . $module_id, 1);
        set_config('board3_on_minecraft_table_name_'   . $module_id, str_replace('"', CHR(96), 'LogBlock."lb-players"'));
        set_config('board3_on_minecraft_player_field_' . $module_id, 'playername');
        set_config('board3_on_minecraft_login_field_'  . $module_id, 'lastlogin');
        set_config('board3_on_minecraft_online_time_'  . $module_id, 'onlinetime');
		return true;
	}

    /**
     * @param  int  $module_id The ID of the instance of this module.
     * @return bool Whether the module uninstalled correctly.
     */
	public function uninstall($module_id)
	{
		global $db;

		$del_config = array(
            'board3_on_minecraft_server_url_'   . $module_id,
            'board3_on_minecraft_sb_port_'      . $module_id,
			'board3_on_minecraft_url_key_'      . $module_id,
            'board3_on_minecraft_avatar_size_'  . $module_id,
            'board3_on_minecraft_nobody_on_'    . $module_id,
            'board3_on_minecraft_num_recent_'   . $module_id,
            'board3_on_minecraft_recent_date_'  . $module_id,
            'board3_on_minecraft_show_total_'   . $module_id,
            'board3_on_minecraft_table_name_'   . $module_id,
            'board3_on_minecraft_player_field_' . $module_id,
            'board3_on_minecraft_login_field_'  . $module_id,
            'board3_on_minecraft_online_time_'  . $module_id,
		);
		$sql = 'DELETE FROM ' . CONFIG_TABLE . '
			WHERE ' . $db->sql_in_set('config_name', $del_config);
		return $db->sql_query($sql);
	}
}
