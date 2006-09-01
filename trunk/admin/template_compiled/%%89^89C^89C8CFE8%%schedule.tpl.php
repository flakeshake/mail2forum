<?php /* Smarty version 2.6.14, created on 2006-09-02 00:06:57
         compiled from schedule.tpl */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'header.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<?php if (isset ( $this->_tpl_vars['display_options'] )): ?>
	<h2>Schedule</h2>
	<form action="schedule.php" method="post">
		<input type="submit" name="action" value="<?php echo $this->_tpl_vars['lang']['run_all_chains']; ?>
" />
	</form>
<?php endif; ?>

<?php if (isset ( $this->_tpl_vars['results'] )): ?>
	<p>Worked!</p>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'footer.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>