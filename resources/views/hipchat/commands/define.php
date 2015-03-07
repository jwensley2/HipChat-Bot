<?php
/** @var array $definitions [] */
?>
<?php foreach ($definitions as $definition): ?>
    <p>
        <strong>Word:</strong> <a href="<?= $definition['permalink'] ?>"><?= $definition['word'] ?></a><br>
        <strong>Definition:</strong> <?= nl2br($definition['definition']) ?><br>
        <strong>Example:</strong> <?= nl2br($definition['example']) ?>
    </p>

    <?php if ($definition !== end($definitions)): ?>
        <br><br>
    <?php endif; ?>
<?php endforeach; ?>
