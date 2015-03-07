<?php
/** @var App\HipChat\Commands\CommandInterface[] $commands */
/** @var string $botname */
/** @var string $botcommand */
?>

<strong><?= $botname ?> has the following commands available</strong><br>
<?php foreach ($commands as $command): ?>
    <br>
    <?= view('hipchat.command-help')->with(['prefix' => $botcommand, 'command' => $command]) ?>
<?php endforeach ?>
