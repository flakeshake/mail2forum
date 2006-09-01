{include file='header.tpl'}
{if isset($config_form)}
	<h2>Enter basic configuration</h2>
	<form action="install.php" method="post">
		<table>
<!-- 
			<tr><th>{$lang.log_file}</th><td><input type="text" name="log_file" size="20" value="{$log_file}" /></td></tr>
 -->
			<tr><th>{$lang.language}</th><td>{html_options name=language options=$languages selected=$language}</td></tr>
			<tr><th>{$lang.db_host}</th><td><input type="text" name="db_host" size="20" value="{$db_host}" /></td></tr>
			<tr><th>{$lang.db_user}</th><td><input type="text" name="db_user" size="20" value="{$db_user}" /></td></tr>
			<tr><th>{$lang.db_pass}</th><td><input type="text" name="db_pass" size="20" value="{$db_pass}" /></td></tr>
			<tr><th>{$lang.db_database}</th><td><input type="text" name="db_database" size="20" value="{$db_database}" /></td></tr>
			<tr><th>{$lang.db_type}</th><td>{html_options name=db_type options=$db_types selected=$db_type}</td></tr>
			<tr><th>{$lang.db_prefix}</th><td><input type="text" name="db_prefix" size="10" value="{$db_prefix}" /></td></tr>
		</table>
		<input type="submit" name="action" value="{$lang.save_config}" />
	</form>
{/if}

{if isset($install_database)}
	<h2>Install database tables</h2>
	<form action="install.php" method="post">
		<input type="submit" name="action" value="{$lang.install_db}" />
	</form>
{/if}

{if isset($initialise_database)}
	<h2>Initialise database tables</h2>
	<form action="install.php" method="post">
		<input type="submit" name="action" value="{$lang.init_db}" />
	</form>
{/if}

{include file='footer.tpl'}