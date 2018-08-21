<?php

namespace Sylake\AkeneoProducerBundle\Connector\Writer;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

final class RabbitMqItemWriter implements ItemWriterInterface
{
    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @var string
     */
    private $messageType;

    /**
     * @param ProducerInterface $producer
     * @param string $messageType
     */
    public function __construct(ProducerInterface $producer, $messageType)
    {
        $this->producer = $producer;
        $this->messageType = $messageType;
    }

    /**
     * @param array $items
     */
    public function write(array $items)
    {
        foreach ($items as $item) {
            $json = json_encode([
                'type' => $this->messageType,
                'payload' => $item,
                'recordedOn' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);

            if ($json !== false) {
                $this->producer->publish($json);
            }
        }
    }
}
