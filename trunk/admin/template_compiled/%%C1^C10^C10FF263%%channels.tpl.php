<?php /* Smarty version 2.6.14, created on 2006-08-31 00:00:27
         compiled from channels.tpl */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'header.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  if (isset ( $this->_tpl_vars['list_uninstalled_channels'] )): ?>
	<h2><?php echo $this->_tpl_vars['lang']['install_channels']; ?>
</h2>
	<form action="channels.php" name="channels" method="post">
<?php if (isset ( $this->_tpl_vars['chain_id'] )): ?>
		<input type="hidden" name="chain_id" value="<?php echo $this->_tpl_vars['chain_id']; ?>
" />
<?php endif; ?>
		<table>
			<tr class="header"><?php if ($this->_tpl_vars['uninstalled_channels']): ?><th class="toggle"><a href="javascript:void(null);" onclick="checkAll('channel_path[]')">&#8595;</a></th><?php endif; ?><th><?php echo $this->_tpl_vars['lang']['channel']; ?>
</th><th><?php echo $this->_tpl_vars['lang']['channel_path']; ?>
</th></tr>
<?php unset($this->_sections['uninstalled']);
$this->_sections['uninstalled']['name'] = 'uninstalled';
$this->_sections['uninstalled']['loop'] = is_array($_loop=$this->_tpl_vars['uninstalled_channels']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['uninstalled']['show'] = true;
$this->_sections['uninstalled']['max'] = $this->_sections['uninstalled']['loop'];
$this->_sections['uninstalled']['step'] = 1;
$this->_sections['uninstalled']['start'] = $this->_sections['uninstalled']['step'] > 0 ? 0 : $this->_sections['uninstalled']['loop']-1;
if ($this->_sections['uninstalled']['show']) {
    $this->_sections['uninstalled']['total'] = $this->_sections['uninstalled']['loop'];
    if ($this->_sections['uninstalled']['total'] == 0)
        $this->_sections['uninstalled']['show'] = false;
} else
    $this->_sections['uninstalled']['total'] = 0;
if ($this->_sections['uninstalled']['show']):

            for ($this->_sections['uninstalled']['index'] = $this->_sections['uninstalled']['start'], $this->_sections['uninstalled']['iteration'] = 1;
                 $this->_sections['uninstalled']['iteration'] <= $this->_sections['uninstalled']['total'];
                 $this->_sections['uninstalled']['index'] += $this->_sections['uninstalled']['step'], $this->_sections['uninstalled']['iteration']++):
$this->_sections['uninstalled']['rownum'] = $this->_sections['uninstalled']['iteration'];
$this->_sections['uninstalled']['index_prev'] = $this->_sections['uninstalled']['index'] - $this->_sections['uninstalled']['step'];
$this->_sections['uninstalled']['index_next'] = $this->_sections['uninstalled']['index'] + $this->_sections['uninstalled']['step'];
$this->_sections['uninstalled']['first']      = ($this->_sections['uninstalled']['iteration'] == 1);
$this->_sections['uninstalled']['last']       = ($this->_sections['uninstalled']['iteration'] == $this->_sections['uninstalled']['total']);
?>
			<tr><td><input type="checkbox" name="channel_path[]" value="<?php echo $this->_tpl_vars['uninstalled_channels'][$this->_sections['uninstalled']['index']]['path']; ?>
" /></td><td><?php echo $this->_tpl_vars['uninstalled_channels'][$this->_sections['uninstalled']['index']]['name']; ?>
</td><td><?php echo $this->_tpl_vars['uninstalled_channels'][$this->_sections['uninstalled']['index']]['path']; ?>
</td></tr>
<?php endfor; else: ?>
			<tr><td colspan="2" class="centered"><?php echo $this->_tpl_vars['lang']['no_uninstalled_channels']; ?>
</td></tr>
<?php endif; ?>
		</table>
	<?php if ($this->_tpl_vars['uninstalled_channels']): ?>
	<input type="submit" name="action" value="<?php echo $this->_tpl_vars['lang']['install_selected_channels']; ?>
" />
	<?php endif; ?>
	</form>
<?php endif; ?>

<?php if (isset ( $this->_tpl_vars['list_installed_channels'] )): ?>
	<h2><?php echo $this->_tpl_vars['lang']['channels']; ?>
</h2>
	<form action="channels.php" method="post">
		<table>
			<tr class="header"><th><?php echo $this->_tpl_vars['lang']['channel']; ?>
</th><th><?php echo $this->_tpl_vars['lang']['channel_path']; ?>
</th></tr>
<?php unset($this->_sections['installed']);
$this->_sections['installed']['name'] = 'installed';
$this->_sections['installed']['loop'] = is_array($_loop=$this->_tpl_vars['installed_channels']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['installed']['show'] = true;
$this->_sections['installed']['max'] = $this->_sections['installed']['loop'];
$this->_sections['installed']['step'] = 1;
$this->_sections['installed']['start'] = $this->_sections['installed']['step'] > 0 ? 0 : $this->_sections['installed']['loop']-1;
if ($this->_sections['installed']['show']) {
    $this->_sections['installed']['total'] = $this->_sections['installed']['loop'];
    if ($this->_sections['installed']['total'] == 0)
        $this->_sections['installed']['show'] = false;
} else
    $this->_sections['installed']['total'] = 0;
if ($this->_sections['installed']['show']):

            for ($this->_sections['installed']['index'] = $this->_sections['installed']['start'], $this->_sections['installed']['iteration'] = 1;
                 $this->_sections['installed']['iteration'] <= $this->_sections['installed']['total'];
                 $this->_sections['installed']['index'] += $this->_sections['installed']['step'], $this->_sections['installed']['iteration']++):
$this->_sections['installed']['rownum'] = $this->_sections['installed']['iteration'];
$this->_sections['installed']['index_prev'] = $this->_sections['installed']['index'] - $this->_sections['installed']['step'];
$this->_sections['installed']['index_next'] = $this->_sections['installed']['index'] + $this->_sections['installed']['step'];
$this->_sections['installed']['first']      = ($this->_sections['installed']['iteration'] == 1);
$this->_sections['installed']['last']       = ($this->_sections['installed']['iteration'] == $this->_sections['installed']['total']);
?>
			<tr><th><?php echo $this->_tpl_vars['installed_channels'][$this->_sections['installed']['index']]->name; ?>
</th><td><?php echo $this->_tpl_vars['installed_channels'][$this->_sections['installed']['index']]->path; ?>
</td></tr>
<?php endfor; endif; ?>
		</table>
	</form>
	<?php if (isset ( $this->_tpl_vars['show_install_channel_link'] )): ?><a href="channels.php?view=uninstalled_channels"><?php echo $this->_tpl_vars['lang']['install_channels']; ?>
</a><?php endif;  endif;  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'footer.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>