<?php
/** @var App\HipChat\Commands\CommandInterface[] $commands */
/** @var string $botname */
/** @var string $botcommand */
?>

<strong><?= $botname ?> :: Command List</strong><br>

<?php foreach ($commands as $command): ?>
    <?= $command->getName() ?> - <?= $command->getDescription() ?>
    <p>/<?= $botcommand ?> <?= $command->getCommand() ?> [max]</p>
<?php endforeach ?>
