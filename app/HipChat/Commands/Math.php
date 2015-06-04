<?php
namespace App\HipChat\Commands;

use App\HipChat\CommandParser;
use App\HipChat\Webhooks\Events\RoomMessage;

class Math extends AbstractCommand implements CommandInterface
{
    protected $command     = 'math';
    protected $name        = 'Math';
    protected $description = 'Perform math operations';
    protected $usage       = '<expression>';
    protected $aliases     = ['calc'];

    /**
     * Triggers the command
     *
     * @param CommandParser $command
     * @param RoomMessage   $event
     * @return void
     */
    public function trigger(CommandParser $command, RoomMessage $event)
    {
        $roomId = $event->item->room->id;

        $value = $command->getMessage();

        $compiler = \Hoa\Compiler\Llk::load(
            new \Hoa\File\Read('hoa://Library/Math/Arithmetic.pp')
        );

        $visitor = new \Hoa\Math\Visitor\Arithmetic();

        if (empty($value)) {
            $this->sendMessage($roomId, "Usage: {$this->getUsage()}");

            return;
        }

        try {
            $ast = $compiler->parse($value);
            $this->sendMessage($roomId, "Result: " . (string)$visitor->visit($ast));
        } catch (\Exception $e) {
            $this->sendMessage($roomId, $e->getMessage());
        }
    }
}
