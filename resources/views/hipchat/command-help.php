<?php
/** @var App\HipChat\Commands\CommandInterface $command */
/** @var string $prefix */
?>


<strong><?= $command->getName() ?> - <?= $command->getDescription() ?></strong><br>
Usage: /<?= $prefix ?> <?= $command->getUsage() ?><br>
Aliases: <?= implode(', ', $command->getAliases()) ?><br>
