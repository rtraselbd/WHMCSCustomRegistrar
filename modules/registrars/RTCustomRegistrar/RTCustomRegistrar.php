<?php

/**
 * RT Custom Registrar Module for WHMCS
 *
 * @author Rasel Islam Rafi
 * @copyright Copyright (c) 2023
 * @license https://uddoktapay.com
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Module\Registrar\RTCustomRegistrar\RTCore;

/**
 * Metadata
 *
 * @return array
 */

function RTCustomRegistrar_MetaData()
{
    return [
        'DisplayName' => 'RT Custom Registrar',
        'APIVersion' => '1.1',
    ];
}


function RTCustomRegistrar_getConfigArray()
{
    return [
        'FriendlyName' => [
            'Type' => 'System',
            'Value' => 'RT Custom Registrar module for WHMCS',
        ]
    ];
}

function RTCustomRegistrar_RegisterDomain($params)
{
    try {

        $response = RTCore::registerDomain($params);

        return [
            'success' => true,
        ];
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
        ];
    }
}

function RTCustomRegistrar_TransferDomain($params)
{
    try {

        $response = RTCore::transferDomain($params);

        return [
            'success' => true,
        ];
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
        ];
    }
}

function RTCustomRegistrar_GetNameservers($params)
{
    try {

        $response = RTCore::getNameservers($params);

        return [
            'ns1' => $response['ns1'],
            'ns2' => $response['ns2'],
            'ns3' => $response['ns3'],
            'ns4' => $response['ns4'],
            'ns5' => $response['ns5']
        ];
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
        ];
    }
}

function RTCustomRegistrar_SaveNameservers($params)
{
    try {

        $response = RTCore::saveNameservers($params);

        return [
            'success' => true,
        ];
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
        ];
    }
}


function RTCustomRegistrar_GetContactDetails($params)
{
    try {

        return RTCore::getContactDetails($params);
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
        ];
    }
}

function RTCustomRegistrar_SaveContactDetails($params)
{
    try {

        $response = RTCore::saveContactDetails($params);

        return [
            'success' => true,
        ];
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
        ];
    }
}

function RTCustomRegistrar_GetDNS($params)
{
    try {

        return  RTCore::getDNS($params);
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
        ];
    }
}


function RTCustomRegistrar_SaveDNS($params)
{
    try {

        $response = RTCore::saveDNS($params);

        return [
            'success' => true,
        ];
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
        ];
    }
}

function RTCustomRegistrar_IDProtectToggle($params)
{
    try {

        $response = RTCore::saveIDProtect($params);

        return [
            'success' => true,
        ];
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
        ];
    }
}
