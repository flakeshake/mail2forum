<?php /* Smarty version 2.6.14, created on 2006-08-30 23:55:36
         compiled from header.tpl */ ?>
<?php echo '<?xml'; ?>
 version="1.0" encoding="iso-8859-1"<?php echo '?>'; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
	<title><?php echo $this->_tpl_vars['lang']['title']; ?>
</title>
	<link rel="stylesheet" href="css/main.css" type="text/css" media="all" />
<?php echo '

	<script language="Javascript" type="text/javascript">

		var all_checked = false;
		function checkAll(checkWhat) {
			var inputs = document.getElementsByTagName("input");
			all_checked = !all_checked;
			for (index = 0; index < inputs.length; index++) {
				if (inputs[index].name == checkWhat) {
					inputs[index].checked = all_checked;
				}
			}
		}        
		
	</script>
'; ?>

</head>
<body>

<?php if (! isset ( $this->_tpl_vars['hide_menu'] )):  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'menubar.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
  endif; ?>
	<h1><?php echo $this->_tpl_vars['lang']['title']; ?>
</h1>
<?php if (isset ( $this->_tpl_vars['message'] )): ?>
	<div class="message"><p><?php echo $this->_tpl_vars['message']; ?>
</p></div>
<?php endif;  if (isset ( $this->_tpl_vars['errors'] )): ?>
	<div class="error"><span class="lable"><?php echo $this->_tpl_vars['lang']['error']; ?>
:</span>
<?php unset($this->_sections['errors']);
$this->_sections['errors']['name'] = 'errors';
$this->_sections['errors']['loop'] = is_array($_loop=$this->_tpl_vars['errors']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['errors']['show'] = true;
$this->_sections['errors']['max'] = $this->_sections['errors']['loop'];
$this->_sections['errors']['step'] = 1;
$this->_sections['errors']['start'] = $this->_sections['errors']['step'] > 0 ? 0 : $this->_sections['errors']['loop']-1;
if ($this->_sections['errors']['show']) {
    $this->_sections['errors']['total'] = $this->_sections['errors']['loop'];
    if ($this->_sections['errors']['total'] == 0)
        $this->_sections['errors']['show'] = false;
} else
    $this->_sections['errors']['total'] = 0;
if ($this->_sections['errors']['show']):

            for ($this->_sections['errors']['index'] = $this->_sections['errors']['start'], $this->_sections['errors']['iteration'] = 1;
                 $this->_sections['errors']['iteration'] <= $this->_sections['errors']['total'];
                 $this->_sections['errors']['index'] += $this->_sections['errors']['step'], $this->_sections['errors']['iteration']++):
$this->_sections['errors']['rownum'] = $this->_sections['errors']['iteration'];
$this->_sections['errors']['index_prev'] = $this->_sections['errors']['index'] - $this->_sections['errors']['step'];
$this->_sections['errors']['index_next'] = $this->_sections['errors']['index'] + $this->_sections['errors']['step'];
$this->_sections['errors']['first']      = ($this->_sections['errors']['iteration'] == 1);
$this->_sections['errors']['last']       = ($this->_sections['errors']['iteration'] == $this->_sections['errors']['total']);
?>
		<p class="<?php echo $this->_tpl_vars['errors'][$this->_sections['errors']['index']]['type']; ?>
"><?php echo $this->_tpl_vars['errors'][$this->_sections['errors']['index']]['message']; ?>
</p>
<?php endfor; endif; ?>
	</div>
<?php endif; ?>