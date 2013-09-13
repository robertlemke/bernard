<?php

namespace Bernard\Serializer;

use Bernard\Message\DefaultMessage;
use Bernard\Envelope;
use Bernard\Verify;

/**
 * Very simple Serializer that only supports the core message types
 * DefaultMessage and FailedMessage. For other Message instances and more
 * advanced needs you should use Symfony or JMS Serializer components.
 *
 * @package Bernard
 */
class NaiveSerializer implements \Bernard\Serializer
{
    /**
     * {@inheritDoc}
     */
    public function serialize(Envelope $envelope)
    {
        Verify::any($envelope->getClass(), array('Bernard\Message\DefaultMessage', 'Bernard\Message\FailedMessage'));

        $message = $envelope->getMessage();

        return json_encode(array(
            'args'      => array('name' => $message->getName()) + get_object_vars($message),
            'class'     => bernard_encode_class_name($envelope->getClass()),
            'timestamp' => $envelope->getTimestamp(),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function deserialize($serialized)
    {
        // everything is just deserialized into an DefaultMessage
        $data = json_decode($serialized, true);
        $data['class'] = bernard_decode_class_string($data['class']);

        if ($data['class'] !== 'Bernard\Message\DefaultMessage') {
            $data['args']['name'] = current(array_reverse(explode('\\', $data['class'])));
        }

        $envelope = new Envelope(new DefaultMessage($data['args']['name'], $data['args']));

        foreach (array('timestamp', 'class') as $name) {
            bernard_force_property_value($envelope, $name, $data[$name]);
        }

        return $envelope;
    }
}
