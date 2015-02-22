<?php
namespace App\HipChat\Webhooks\Events;

use App\HipChat\Webhooks\Item;

/**
 * @property-read string $oAuthClientId
 * @property-read string $event
 * @property-read string $webhookId
 * @property-read Item   $item
 */
class Event
{
    /** @var string */
    protected $event;

    /** @var string */
    protected $oAuthClientId;

    /** @var int */
    protected $webhookId;

    /** @var Item */
    protected $item;

    public function __construct(\StdClass $data)
    {
        $this->event = $data->event;
        $this->item = new Item($data->item);
        $this->oAuthClientId = $data->oauth_client_id;
        $this->webookId = $data->webhook_id;
    }

    public function __get($prop)
    {
        if (isset($this->{$prop})) {
            return $this->{$prop};
        }

        return null;
    }
}