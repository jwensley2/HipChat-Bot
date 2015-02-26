<?php
/** @var \App\Event[] $events */
?>

<strong>Upcoming Events</strong><br>
<?php foreach ($events as $event): ?>
    <p>
        {<?= $event->id ?>} <strong><?= $event->date->format('Y-m-d H:i') ?></strong> - <?= $event->description ?>
    </p>
<?php endforeach; ?>
