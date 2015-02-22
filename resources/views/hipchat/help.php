<?php
/** @var App\HipChat\Commands\CommandInterface[] $commands */
/** @var string $botname */
/** @var string $botcommand */
?>

<strong><?= $botname ?> has the following commands available</strong><br>
<?php foreach ($commands as $command): ?>
    <br>
    <strong><?= $command->getName() ?> - <?= $command->getDescription() ?></strong><br>
    Usage: /<?= $botcommand ?> <?= $command->getUsage() ?><br>
    Aliases: <?= implode(', ', $command->getAliases()) ?><br>
<?php endforeach ?>