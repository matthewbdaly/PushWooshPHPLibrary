<?php

namespace PushWoosh;

class PushWoosh
{
    public function __construct($config)
    {
        // Set the config options up
        $this->config = $config;
    }

    /*
     * Sends a POST request to create the push message
     * @param string $url The URL to send the POST request to
     * @param string $data The data to be sent, encoded as JSON
     * @param string $optional_headers Any optional headers. Defaults to null
     * @return mixed Returns the response, or false if nothing received
     * @author Matthew Daly
     */
    private function doPostRequest($url, $data, $optional_headers = null)
    {
        $params = array(
            'http' => array(
                'method' => 'POST',
                'content' => $data
            ));
        if($optional_headers !== null)
        {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = fopen($url, 'rb', false, $ctx);
        if(!$fp)
        {
            throw new Exception("Problem with $url, $php_errmsg");
        }

        $response = @stream_get_contents($fp);
        if($response === false)
        {
            return false;
        }
        return $response;
    }

    /*
     * Puts together the POST request to create the push message
     * @param string $action The action to take
     * @param array $data The data to send
     * @return bool Confirms that the method executed
     * @author Matthew Daly
     */
    private function pwCall($action, $data = array())
    {
        $url = 'https://cp.pushwoosh.com/json/1.2/'.$action;
        $json = json_encode(array('request' => $data));
        $res = $this->doPostRequest($url, $json, 'Content-Type: application/json');
    }

    /*
     * Creates a push message using PushWoosh
     * @param string $pushes An array containing the message and device token for each push notification to be sent
     * @param string $sendDate Send date of the message. Defaults to right now
     * @param string $link A link to follow when the push notification is clicked. Defaults to null
     * @return bool Confirms that the method executed
     * @author Matthew Daly
     */
    public function createMessage($pushes, $sendDate = 'now', $link = null)
    {
        // Get the config settings
        $config = $this->config;

        // Store the message data
        $data = array(
            'application' => $config['push']['application'],
            'username' => $config['push']['username'],
            'password' => $config['push']['password']
        );

        // Loop through each push and add them to the notifications array
        foreach ($pushes as $push)
        {
            $pushData = array(
                'send_date' => $sendDate,
                'content' => $push['content'],
                'ios_badges' => 3,
                'devices' => $push['devices']
            );
            $data['notifications'][] = $pushData;
        }

        // Send the message
        $this->pwCall('createMessage', $data);

        // Return a value
        return true;
    }
}
?>
