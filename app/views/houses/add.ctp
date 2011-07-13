<div class="houses form">
<?php echo $this->Form->create('House');?>
	<fieldset>
		<legend><?php __('Add House'); ?></legend>
	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('address', array('rows' => 3));
		echo $this->Form->input('User');
		pr($users);
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Houses', true), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List Users', true), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New User', true), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
