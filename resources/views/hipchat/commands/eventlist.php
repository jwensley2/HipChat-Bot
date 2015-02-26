<?php
/** @var \App\Event[] $events */
?>

<strong>Events</strong><br>
<?php foreach ($events as $event): ?>
    <p>
        <strong><?= $event->date->format('Y-m-d H:i') ?></strong> - <?= $event->description ?>
    </p>
<?php endforeach; ?>
