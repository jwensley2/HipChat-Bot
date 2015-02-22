<?php
namespace App\HipChat\Webhooks\Events;

use App\HipChat\Webhooks\Item;

/**
 * @property-read string $oAuthClientId
 * @property-read string $event
 * @property-read string $webhookId
 * @property-read Item   $item
 */
class RoomMessage extends Event
{
}