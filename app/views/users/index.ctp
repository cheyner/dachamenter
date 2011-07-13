<!-- Here is where we loop through our $users array, printing out user info -->
<?= $session->flash() ?>
<?php foreach ($users as $user): ?>
	<div id="user">
	<h2><?php echo $this->Html->link($user['User']['username'],
			array('controller' => 'users', 'action' => 'view', $user['User']['id'])); ?></h2>
	<div id="taskowned">
		<h3>Owned Tasks</h3>
		<ul>
		<?php foreach ($user['TaskOwned'] as $task): ?>
			<li><?= $task['title'] ?></li>
		<?php endforeach; ?>
		</ul>
	</div>
	<div id="task">
		<h3>Assigned Tasks</h3>
		<ul>
		<?php foreach ($user['Task'] as $task): ?>
			<li><?= $task['title'] ?></li>
		<?php endforeach; ?>
		</ul>
	</div>
	</div>
<?php endforeach; ?>
</ol>
