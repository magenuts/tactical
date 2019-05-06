<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Plugin\Framework\Mail;

use Magento\Framework\Mail\Message;
use \Magetrend\Email\Helper\Data;
use \Magetrend\Email\Helper\Html2Text;

class MessagePlugin
{
    /**
     * @var \Magetrend\Email\Helper\Data
     */
    public $mtHelper;

    /**
     * @var Html2Text
     */
    public $html2Text;

    /**
     * MessagePlugin constructor.
     *
     * @param Data $helper
     * @param Html2Text $html2Text
     */
    public function __construct(
        Data $helper,
        Html2Text $html2Text
    ) {
        $this->mtHelper = $helper;
        $this->html2Text = $html2Text;
    }

    /**
     * Convert html to text and add text version to email
     *
     * @param Message $message
     * @param $body
     * @param null $charset
     * @param string $encoding
     * @return array
     */
    public function beforeSetBodyHtml(Message $message, $body, $charset = null, $encoding = 'quoted-printable')
    {
        $this->html2Text->setHtml($body);
        $textVersion = $this->html2Text->getText();
        $message->setBodyText($textVersion);
        $charset = 'utf-8';
        return [$body, $charset, $encoding];
    }
}
