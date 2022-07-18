<?php

class PayshopHelpers
{
    /**
     * Get the transaction by column
     *
     * @param string $chargeId
     * @return array
     * @throws Exception
     */
    public static function getTransacion($column, $value)
    {
        $transaction = new PayshopTransaction();
        $transaction->where($column, '=', $value);
        $transaction = $transaction->get();

        if (!$transaction) {
            throw new Exception('Transaction not found', 404);
        }

        return $transaction;
    }

    /**
     * Create Payshop event
     *
     * @param string $eventId
     * @param string $eventType
     * @param string $transactionId
     * @return bool
     */
    public static function createEvent($eventId, $eventType, $transactionId)
    {
        $event = new PayshopEventModel();
        $isCreated = $event->create([
            'event_id' => $eventId,
            'event_type' => $eventType,
            'transaction_id' => $transactionId
        ]);

        return $isCreated;
    }
}