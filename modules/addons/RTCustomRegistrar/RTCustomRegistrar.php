<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Database\Capsule;
use WHMCS\Mail\Template as EmailTemplate;
use WHMCS\Module\Registrar\RTCustomRegistrar\RTCore;

function RTCustomRegistrar_config()
{
    return [
        'name' => "RT Custom Registrar for WHMCS",
        'description' => "Manually manage domains",
        'version' => "1.0.0",
        'author' => "<a href='https://github.com/rtraselbd' target='_blank'>Md Rasel Islam</a>",
        'fields' => [
            'DeleteAddonData' => [
                'FriendlyName' => 'Delete Addon Data',
                'Type' => 'yesno',
                'Description' => 'Check the box to activate the option for deleting addon data when deactivating the addon.',
            ],
        ]
    ];
}

function RTCustomRegistrar_activate()
{
    try {

        // Create Table
        if (!Capsule::schema()->hasTable("mod_RTCustomRegistrar")) {
            Capsule::schema()->create("mod_RTCustomRegistrar", function ($table) {
                $table->increments("id");
                $table->text("domainid");
                $table->text("nameserver_data")->nullable();
                $table->text("contact_data")->nullable();
                $table->text("dns_data")->nullable();
            });
        }

        // Create Email Template
        $templates = emailTemplate();
        createEmailTemplate($templates);

        return [
            'status' => 'success',
            'description' => 'RTCustomRegistrar has been activated'
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'description' => 'Could not create email template: ' . $e->getMessage()
        ];
    }
}


function RTCustomRegistrar_deactivate()
{
    try {

        // Get Addon Value
        $deleteAddonData = RTCore::value('tbladdonmodules', ['module' => 'RTCustomRegistrar', 'setting' => 'DeleteAddonData'], 'value');

        if ($deleteAddonData === 'on') {
            // Drop the custom database table if it exists
            Capsule::schema()->dropIfExists("mod_RTCustomRegistrar");

            // Define a list of email template names to delete
            $templateNames = [
                'RTCustomRegistrar Domain Registration',
                'RTCustomRegistrar Domain Transfer',
                'RTCustomRegistrar Domain Nameservers Update',
                'RTCustomRegistrar Domain ContactDetails Update',
                'RTCustomRegistrar Domain DNS Update',
                'RTCustomRegistrar Domain ID Protection'
            ];

            // Delete email templates
            foreach ($templateNames as $templateName) {
                EmailTemplate::where('name', $templateName)->delete();
            }
        }

        return [
            'status' => 'success',
            'description' => 'RTCustomRegistrar has been deactivated',
        ];
    } catch (Exception $e) {
        return [
            'status' => 'error',
            'description' => 'Could not deactivate RTCustomRegistrar: ' . $e->getMessage(),
        ];
    }
}


function emailTemplate()
{
    return [
        [
            'name'      => 'RTCustomRegistrar Domain Registration',
            'subject'   => 'RTCustomRegistrar Domain Registration Notification',
            'message'   => '<p>Dear Admin,</p>
            <p>We are pleased to inform you that the payment for the domain "{$domain}" has been received successfully, and its
                registration process has been completed. Here are the details of the registration:</p>
            
            <p><strong>Client Information:</strong></p>
            <ul>
                <li>Client ID: {$client_id}</li>
                <li>Client Name: {$client_name}</li>
            </ul>
            
            <p><strong>Domain Details:</strong></p>
            <ul>
                <li>Domain: {$domain}</li>
                <li>DNS Management: {$dnsmanagement}</li>
                <li>Email Forwarding: {$emailforwarding}</li>
                <li>ID Protection: {$idprotection}</li>
            </ul>
            
            <p><strong>Nameservers</strong></p>
            <ul>
                {foreach from=$nameservers item=ns key=server}
                <li>{$server}: {$ns}</li>
                {/foreach}
            </ul>
            
            <p><strong>Contact Details</strong></p>
            {foreach from=$contactdetails item=typeData key=typeName}
            <p><strong>{$typeName}</strong></p>
            <ul>
                {foreach from=$typeData item=data key=type}
                <li>{$type}: {$data}</li>
                {/foreach}
            </ul>
            {/foreach}
            
            <p>{$signature}</p>',
        ],
        [
            'name'      => 'RTCustomRegistrar Domain Nameservers Update',
            'subject'   => 'RTCustomRegistrar Domain Nameservers Update Notification',
            'message'   => '<p>Dear Admin,</p>
            <p>We would like to inform you that a domain Nameserver Change request has been submitted for the domain "{$domain}". Please review the details below:</p>
            
            <p><strong>Client Information:</strong></p>
            <ul>
                <li>Client ID: {$client_id}</li>
                <li>Client Name: {$client_name}</li>
            </ul>

            <p><strong>Domain Details:</strong></p>
            <ul>
                <li>Domain: {$domain}</li>
            </ul>

            <p><strong>Requested Nameserver Changes:</strong></p>
            <ul>
                {foreach from=$nameservers item=ns key=server}
                    <li>{$server}: {$ns}</li>
                {/foreach}
            </ul>

            <p>{$signature}</p>'
        ],
        [
            'name'      => 'RTCustomRegistrar Domain Transfer',
            'subject'   => 'RTCustomRegistrar Domain Transfer Notification',
            'message'   => '<p>Dear Admin,</p>
            <p>We would like to inform you that a domain Transfer request has been submitted for the domain "{$domain}". Please review the details below:</p>
            
            <p><strong>Client Information:</strong></p>
            <ul>
                <li>Client ID: {$client_id}</li>
                <li>Client Name: {$client_name}</li>
            </ul>

            <p><strong>Domain Details:</strong></p>
            <ul>
                <li>Domain: {$domain}</li>
                <li>DNS Management: {$dnsmanagement}</li>
                <li>Email Forwarding: {$emailforwarding}</li>
                <li>ID Protection: {$idprotection}</li>
                <li>EPP/Auth Code: {$epp_code}</li>
            </ul>

            <p><strong>Nameservers:</strong></p>
            <ul>
                {foreach from=$nameservers item=ns key=server}
                    <li>{$server}: {$ns}</li>
                {/foreach}
            </ul>

            <p>{$signature}</p>'
        ],
        [
            'name'      => 'RTCustomRegistrar Domain ContactDetails Update',
            'subject'   => 'RTCustomRegistrar Domain ContactDetails Update Notification',
            'message'   => '<p>Dear Admin,</p>
            <p>We would like to inform you that a domain Contact Details Update request has been submitted for the domain "{$domain}". Please review the details below:</p>
            
            <p><strong>Client Information:</strong></p>
            <ul>
                <li>Client ID: {$client_id}</li>
                <li>Client Name: {$client_name}</li>
            </ul>

            <p><strong>Domain Details:</strong></li>
            <ul>
                <li>Domain: {$domain}</li>
            </ul>

            <p><strong>Contact Details</strong></p>
            {foreach from=$contactdetails item=typeData key=typeName}
            <p><strong>{$typeName}</strong></p>
            <ul>
                {foreach from=$typeData item=data key=type}
                    <li>{$type}: {$data}</li>
                {/foreach}
            </ul>
            {/foreach}

            <p>{$signature}</p>',
        ],
        [
            'name'      => 'RTCustomRegistrar Domain DNS Update',
            'subject'   => 'RTCustomRegistrar Domain DNS Update Notification',
            'message'   => '<p>Dear Admin,</p>
            <p>We would like to inform you that a domain DNS Update request has been submitted for the domain "{$domain}". Please review the details below:</p>
            
            <p><strong>Client Information:</strong></p>
            <ul>
                <li>Client ID: {$client_id}</li>
                <li>Client Name: {$client_name}</li>
            </ul>

            <p><strong>Domain Details:</strong></p>
            <ul>
                <li>Domain: {$domain}</li>
            </ul>

            <p><strong>DNS Details</strong></p>
            {foreach from=$dnsrecords item=typeData key=typeName}
            <p><strong>Record</strong></p>
            <ul>
                {foreach from=$typeData item=data key=type}
                    <li>{$type}: {$data}</li>
                {/foreach}
            </ul>
            {/foreach}

            <p>{$signature}</p>',
        ],
        [
            'name'      => 'RTCustomRegistrar Domain ID Protection',
            'subject'   => 'RTCustomRegistrar Domain ID Protection Notification',
            'message'   => '<p>Dear Admin,</p>
             <p>We would like to inform you that a domain ID Protection {$idprotection} request has been submitted for the domain "{$domain}". Please review the details below:</p>
            
            <p><strong>Client Information:</strong></p>
            <ul>
                <li>Client ID: {$client_id}</li>
                <li>Client Name: {$client_name}</li>
            </ul>

            <p><strong>Domain Details:</strong></p>
            <ul>
                <li>Domain: {$domain}</li>
                <li>ID Protection: {$idprotection}</li>
            </ul>

            <p>{$signature}</p>',
        ],
    ];
}

function createEmailTemplate($templates)
{
    foreach ($templates as $template) {
        if (!templateExists($template['name'])) {
            try {
                $emailTemplate = new EmailTemplate([
                    'type'      => 'admin',
                    'custom'    => 1,
                    'name'      => $template['name'],
                    'subject'   => $template['subject'],
                    'message'   => $template['message']
                ]);

                $emailTemplate->save();
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
    }
}

function templateExists($templateName)
{
    $existingTemplate = Capsule::table('tblemailtemplates')
        ->where('name', $templateName)
        ->first();

    return !empty($existingTemplate);
}
