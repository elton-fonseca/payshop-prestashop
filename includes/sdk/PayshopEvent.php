<?php

class PayshopEvent
{
    private function __construct(){}

    /**
     * Get the event data from the request.
     *
     * @param boolean $isTestEnvironment If test environment, create request file log.
     * @return array
     * @throws Exception
     */
    static public function getEvent($isTestEnvironment = false)
    {
        $eventData = $_GET;

        self::checkEventData($eventData);

        if ($isTestEnvironment) {
            self::generateFileLog();
        }

        return $eventData;
    }

    /**
     * Check the event data.
     *
     * @param array $eventData
     * @return void
     * @throws Exception
     */
    static private function checkEventData($eventData)
    {
        $eventIsInvalid = !isset($eventData['event']) || !isset($eventData['event_type']) || !isset($eventData['schema']);

        if ($eventIsInvalid) {
            throw new Exception('Event is not received');
        }
    }

    /**
     * Create a file log with the request data.
     *
     * @return void
     * @throws Exception
     */
    static private function generateFileLog()
    {
        $file = fopen('event-' . date('Y-m-d-H-i-s') . '.txt', 'a');

        if (!$file) {
            throw new Exception('File log could not be created');
        }

        fwrite($file, self::getRequestInformations());
        fclose($file);
    }

    /**
     * Get the request informations.
     *
     * @return string
     */
    static private function getRequestInformations()
    {
        $requestInformations = 'server: ' . var_export($_SERVER, true) . PHP_EOL;
        $requestInformations .= 'post: ' .  var_export($_POST, true) . PHP_EOL;
        $requestInformations .= 'get: ' . var_export($_GET, true) . PHP_EOL;
        $requestInformations .= 'files: ' . var_export($_FILES, true) . PHP_EOL;
        $requestInformations .= 'request: ' . var_export($_REQUEST, true) . PHP_EOL;
        $requestInformations .= 'body: ' . var_export(file_get_contents('php://input'), true) . PHP_EOL;

        return $requestInformations;
    }
}