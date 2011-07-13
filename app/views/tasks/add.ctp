<div class="tasks form">
<?php echo $this->Form->create('Task');?>
	<fieldset>
		<legend><?php __('Add Task'); ?></legend>
	<?php
		echo $this->Form->input('assignee_id');
		echo $this->Form->input('house_id');
		echo $this->Form->input('owner_id');
		echo $this->Form->input('title');
		echo $this->Form->input('description');
		echo $this->Form->input('is_completed');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<div class="actions">
	<h3><?php __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Tasks', true), array('action' => 'index'));?></li>
	</ul>
</div>