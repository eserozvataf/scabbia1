<?php
/**
 * Scabbia Framework Version 1.5
 * https://github.com/eserozvataf/scabbia1
 * Eser Ozvataf, eser@ozvataf.com
 */

namespace Scabbia\Extensions\Smtp;

use Scabbia\Extensions\Mime\Mime;
use Scabbia\Extensions\Mime\Mimepart;
use Scabbia\Extensions\Mime\Multipart;
use Scabbia\Extensions\Smtp\Smtp;

/**
 * Smtp Extension: Mail Class
 *
 * @package Scabbia
 * @subpackage Smtp
 * @version 1.1.0
 */
class Mail
{
    /**
     * @ignore
     */
    public $from;
    /**
     * @ignore
     */
    public $to;
    /**
     * @ignore
     */
    public $subject;
    /**
     * @ignore
     */
    public $headers = array();
    /**
     * @ignore
     */
    public $content;
    /**
     * @ignore
     */
    public $parts = array();


    /**
     * @ignore
     */
    public function addPart($uFilename, $uContent, $uEncoding = '8bit', $uType = null)
    {
        $tMimepart = new Mimepart();
        $tMimepart->filename = $uFilename;

        if ($uType !== null) {
            $tMimepart->type = $uType;
        } else {
            $tExtension = pathinfo($uFilename, PATHINFO_EXTENSION);
            $tMimepart->type = Mime::getType($tExtension, 'text/plain');
        }


        $tMimepart->transferEncoding = $uEncoding;
        $tMimepart->content = $uContent;

        $this->parts[] = $tMimepart;

        return $tMimepart;
    }

    /**
     * @ignore
     */
    public function addAttachment($uFilename, $uPath, $uEncoding = 'base64', $uType = null)
    {
        $tMimepart = new Mimepart();
        $tMimepart->filename = $uFilename;

        if ($uType !== null) {
            $tMimepart->type = $uType;
        } else {
            $tExtension = pathinfo($uFilename, PATHINFO_EXTENSION);
            $tMimepart->type = Mime::getType($tExtension, 'application/octet-stream');
        }

        $tMimepart->transferEncoding = $uEncoding;
        $tMimepart->load($uPath);

        $this->parts[] = $tMimepart;

        return $tMimepart;
    }

    /**
     * @ignore
     */
    public function getContent()
    {
        $tHeaders = $this->headers;

        if (!isset($tHeaders['From'])) {
            $tHeaders['From'] = $this->from;
        }
        if (!isset($tHeaders['To'])) {
            $tHeaders['To'] = implode(', ', $this->to);
        }
        if (!isset($tHeaders['Subject'])) {
            $tHeaders['Subject'] = $this->subject;
        }

        if (count($this->parts) > 0) {
            $tMain = new Multipart('mail', Multipart::ALTERNATIVE);
            $tMain->filename = 'mail.eml';
            $tMain->content = $this->content;
            $tMain->headers = $tHeaders;

            foreach ($this->parts as $tPart) {
                $tMain->parts[] = $tPart;
            }

            return $tMain->compile();
        }

        $tString = "";
        foreach ($tHeaders as $tKey => $tValue) {
            $tString .= $tKey . ': ' . $tValue . "\n";
        }
        $tString .= "\n" . $this->content;

        return $tString;
    }

    /**
     * @ignore
     */
    public function send()
    {
        // FIXME a temporary solution, next time don't forget the <> characters in quoted strings
        $uToAddresses = array();
        foreach ($this->to as $tToAddress) {
            $tStart = strpos($tToAddress, '<');
            if ($tStart !== false) {
                $tEnd = strpos($tToAddress, '>', $tStart);
                if ($tEnd !== false) {
                    $uToAddresses[] = substr($tToAddress, ++$tStart, $tEnd - $tStart);
                    continue;
                }
            }

            $uToAddresses[] = $tToAddress;
        }

        Smtp::send($this->from, $uToAddresses, $this->getContent());
    }
}
