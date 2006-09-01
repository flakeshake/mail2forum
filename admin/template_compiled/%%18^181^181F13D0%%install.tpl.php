<?php /* Smarty version 2.6.14, created on 2006-09-01 00:22:18
         compiled from install.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'html_options', 'install.tpl', 9, false),)), $this); ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'header.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  if (isset ( $this->_tpl_vars['config_form'] )): ?>
	<h2>Enter basic configuration</h2>
	<form action="install.php" method="post">
		<table>
<!-- 
			<tr><th><?php echo $this->_tpl_vars['lang']['log_file']; ?>
</th><td><input type="text" name="log_file" size="20" value="<?php echo $this->_tpl_vars['log_file']; ?>
" /></td></tr>
 -->
			<tr><th><?php echo $this->_tpl_vars['lang']['language']; ?>
</th><td><?php echo smarty_function_html_options(array('name' => 'language','options' => $this->_tpl_vars['languages'],'selected' => $this->_tpl_vars['language']), $this);?>
</td></tr>
			<tr><th><?php echo $this->_tpl_vars['lang']['db_host']; ?>
</th><td><input type="text" name="db_host" size="20" value="<?php echo $this->_tpl_vars['db_host']; ?>
" /></td></tr>
			<tr><th><?php echo $this->_tpl_vars['lang']['db_user']; ?>
</th><td><input type="text" name="db_user" size="20" value="<?php echo $this->_tpl_vars['db_user']; ?>
" /></td></tr>
			<tr><th><?php echo $this->_tpl_vars['lang']['db_pass']; ?>
</th><td><input type="text" name="db_pass" size="20" value="<?php echo $this->_tpl_vars['db_pass']; ?>
" /></td></tr>
			<tr><th><?php echo $this->_tpl_vars['lang']['db_database']; ?>
</th><td><input type="text" name="db_database" size="20" value="<?php echo $this->_tpl_vars['db_database']; ?>
" /></td></tr>
			<tr><th><?php echo $this->_tpl_vars['lang']['db_type']; ?>
</th><td><?php echo smarty_function_html_options(array('name' => 'db_type','options' => $this->_tpl_vars['db_types'],'selected' => $this->_tpl_vars['db_type']), $this);?>
</td></tr>
			<tr><th><?php echo $this->_tpl_vars['lang']['db_prefix']; ?>
</th><td><input type="text" name="db_prefix" size="10" value="<?php echo $this->_tpl_vars['db_prefix']; ?>
" /></td></tr>
		</table>
		<input type="submit" name="action" value="<?php echo $this->_tpl_vars['lang']['save_config']; ?>
" />
	</form>
<?php endif; ?>

<?php if (isset ( $this->_tpl_vars['install_database'] )): ?>
	<h2>Install database tables</h2>
	<form action="install.php" method="post">
		<input type="submit" name="action" value="<?php echo $this->_tpl_vars['lang']['install_db']; ?>
" />
	</form>
<?php endif; ?>

<?php if (isset ( $this->_tpl_vars['initialise_database'] )): ?>
	<h2>Initialise database tables</h2>
	<form action="install.php" method="post">
		<input type="submit" name="action" value="<?php echo $this->_tpl_vars['lang']['init_db']; ?>
" />
	</form>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'footer.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>