<?php


namespace FreePBX\modules\Queuelog\Traits;


use AGI_AsteriskManager;

trait QueueLiveTrait
{
    public function conect()
    {
        // set to true to echo debug
        $debug=true;

// Set the local device prefix for the local hint, must match what is defined in extensions_custom.conf for the hint
// the $device_prefix will be used to prefix the remote extension number for the hint
        $device_prefix = "Custom:Remote";

// Remote server host/IP, AMI credentials and
        $remote_server = "hostname_placeholder";
        $remote_name = "name";
        $remote_secret = "password";
        $remote_context = "from-internal";

// define range or remote extension numbers to poll, the fewer the better
// the range needs to match the dynamic hint noted above
        $remote_extension_start='1000';
        $remote_extension_end='1010';

// Connect to local machine with FreePBX bootstrap, requires FreePBX 2.9 or higher
        if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) {
            include_once('/etc/asterisk/freepbx.conf');
        }

// connect to remote Asterisk machine using AMI credentials and get status of Extension 103
        $remote = new AGI_AsteriskManager();
        if ($remote->connect($remote_server, $remote_name, $remote_secret)) {
            for ($remote_extension=$remote_extension_start; $remote_extension<=$remote_extension_end; $remote_extension++) {
                $foo[$remote_extension] = $remote->ExtensionState($remote_extension, $remote_context);
            }
            $remote->disconnect();
        }
        else {
            output("Can not connect to remote AGI");
        }

// print_r($foo);  //for debug

// Based on value of remote extension status, change local custom device state to match
// edit $device to reflect name of local custom device as defined in extensions_custom.conf
// in the [from-internal-custom] section add a line similar to:
// exten => 103,hint,Custom:Remote103
// Make sure that the number does not conflict with something else

        if($astman->connected()) {
            for ($remote_extension=$remote_extension_start; $remote_extension<=$remote_extension_end; $remote_extension++) {
                switch ($foo[$remote_extension]['Status']) {
                    case -1:
                        output("$remote_extension Extension not found");
                        $cmd = $astman->Command('devstate change '.$device_prefix.$remote_extension.' UNKNOWN');
                        break;
                    case 0:
                        output("$remote_extension Idle");
                        $cmd = $astman->Command('devstate change '.$device_prefix.$remote_extension.' NOT_INUSE');
                        break;
                    case 1:
                        output("$remote_extension In Use");
                        $cmd = $astman->Command('devstate change '.$device_prefix.$remote_extension.' INUSE');
                        break;
                    case 2:
                        output("$remote_extension Busy");
                        $cmd = $astman->Command('devstate change '.$device_prefix.$remote_extension.' BUSY');
                        break;
                    case 4:
                        output("$remote_extension Unavailable");
                        $cmd = $astman->Command('devstate change '.$device_prefix.$remote_extension.' UNAVAILABLE');
                        break;
                    case 8:
                        output("$remote_extension Ringing");
                        $cmd = $astman->Command('devstate change '.$device_prefix.$remote_extension.' RINGING');
                        break;
                    case 9:
                        output("$remote_extension Ringing");
                        $cmd = $astman->Command('devstate change '.$device_prefix.$remote_extension.' RINGING');
                        break;
                    case 16:
                        output("$remote_extension On Hold");
                        $cmd = $astman->Command('devstate change '.$device_prefix.$remote_extension.' ONHOLD');
                        break;
                    default:
                        output("$remote_extension Extension not found");
                        $cmd = $astman->Command('devstate change '.$device_prefix.$remote_extension.' UNKNOWN');
                }
            }
        } else {
            output("Can not connect to local AGI");
        }

        function output($string){
            global $debug;
            if ($debug) {
                echo $string."\n";
            }
        }
    }
}