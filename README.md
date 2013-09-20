on-minecraft-portal-module
==========================

This is a phpBB modification (mod) that will provide an additional module for Board III Portal to display the users currently logged into your Minecraft server. It was built for the Minecraft server Ancient Shores (http://ancientshores.com) and supports the forum and Minecraft plugin versions on that server.

On the forum we have:

phpBB version 3.0.11 (https://www.phpbb.com/) - Base Forum Software
Board III Portal version 2.0.0 (http://www.board3.de/) - Provides a portal interface for phpBB

On the Minecraft server we have:

SpaceBukkit (http://spacebukkit.xereo.net/) - Provides an admin interface and web services this plugin uses to get the list of currently logged in users
LogBlock (http://dev.bukkit.org/bukkit-plugins/logblock/) - Provides logging services and keeps track of how recently a player has logged in and how much time players have spent on the server and can save its data to a mySQL database.

Without SpaceBukkit this mod would not be able to bring up the current users which would defeat the primary purpose of this plugin.  This mod can run just fine without LogBlock and if there is another plugin installed that stores usernames and last login times this plugin can be configured to pull its values from that database instead.