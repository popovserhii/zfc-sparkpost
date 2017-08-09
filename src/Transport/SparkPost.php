<?php

namespace Popov\ZfcSparkPost\Transport;

use Zend\Mail\Message;
use Zend\Mail\Transport\Exception;
use Zend\Mail\Address;
use Zend\Mail\Transport\TransportInterface;
use SparkPost\SparkPost as Sparky;
use GuzzleHttp\Client;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;


class SparkPost implements TransportInterface
{
    /**
     * SMTP array config
     *
     * @var array
     */
    protected $options = [];

    /**
     * Constructor.
     *
     * @param array $options Optional
     */
    public function __construct($options = null)
    {
        $this->setOptions($options);
    }

    /**
     * Set options
     *
     * @param array $options
     * @return self
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function send(Message $message)
    {
        $options          = $this->getOptions();

        $spec['api_key'] = $options['api_key'];
        unset($options['api_key']);

        // Prepare message
        $from       = $this->prepareFromAddress($message);
        $recipients = $this->prepareRecipients($message);
        $headers    = $this->prepareHeaders($message);
        $body       = $this->prepareBody($message);

        $post = [
            'content' => [
                'from' => [
                    #'name' => 'SparkPost Team',
                    'email' => $from,
                ],
                'subject' => $message->getSubject(),
                'html' => $body,
                #'text' => $body,
            ],
            #'substitution_data' => ['name' => ''],
        ];

        $post = array_merge($post, $recipients);

        if ((count($recipients) == 0) && (! empty($headers) || ! empty($body))) {
            // Per RFC 2821 3.3 (page 18)
            throw new Exception\RuntimeException(
                sprintf(
                    '%s transport expects at least one recipient if the message has at least one header or body',
                    __CLASS__
                )
            );
        }

        //\Zend\Debug\Debug::dump($recipients); die(__METHOD__);

        $httpClient = new GuzzleAdapter(new Client());
        $sparky = new Sparky($httpClient, ['key' => $spec['api_key']]);
        $sparky->setOptions($options);
        $response = $sparky->transmissions->post($post);

        if (200 != $response->getStatusCode()) {
            throw new Exception\RuntimeException(sprintf(
                '%s (%s: %s)',
                $response->getBody()['errors']['description'],
                $response->getBody()['errors']['code'],
                $response->getBody()['errors']['message']
            ));
        }
    }

    /**
     * Retrieve email address for envelope FROM
     *
     * @param  Message $message
     * @throws Exception\RuntimeException
     * @return string
     */
    protected function prepareFromAddress(Message $message)
    {
        #if ($this->getEnvelope() && $this->getEnvelope()->getFrom()) {
        #    return $this->getEnvelope()->getFrom();
        #}

        $sender = $message->getSender();
        if ($sender instanceof Address\AddressInterface) {
            return $sender->getEmail();
        }

        $from = $message->getFrom();
        if (! count($from)) {
            // Per RFC 2822 3.6
            throw new Exception\RuntimeException(sprintf(
                '%s transport expects either a Sender or at least one From address in the Message; none provided',
                __CLASS__
            ));
        }

        $from->rewind();
        $sender = $from->current();
        return $sender->getEmail();
    }

    /**
     * Prepare array of email address recipients
     *
     * @param  Message $message
     * @return array
     */
    protected function prepareRecipients(Message $message)
    {
        #if ($this->getEnvelope() && $this->getEnvelope()->getTo()) {
        #    return (array) $this->getEnvelope()->getTo();
        #}

        $recipients = [];
        $recipients['recipients'] = $this->prepareAddresses($message->getTo());
        !($cc = $this->prepareAddresses($message->getCc())) || $recipients['cc'] = $cc;
        !($bcc = $this->prepareAddresses($message->getBcc())) || $recipients['bcc'] = $bcc;

        return $recipients;
    }

    protected function prepareAddresses($addresses)
    {
        $recipients = [];
        foreach ($addresses as $address) {
            $item = [];
            if ($address->getName()) {
                $item['name'] = $address->getName();
            }
            $item['email'] = $address->getEmail();
            $recipients[]['address'] = $item;
        }

        return $recipients;
    }

    /**
     * Prepare header string from message
     *
     * @param  Message $message
     * @return string
     */
    protected function prepareHeaders(Message $message)
    {
        $headers = clone $message->getHeaders();
        $headers->removeHeader('Bcc');
        return $headers->toString();
    }

    /**
     * Prepare body string from message
     *
     * @param  Message $message
     * @return string
     */
    protected function prepareBody(Message $message)
    {
        return $message->getBodyText();
    }
}