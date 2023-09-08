<?php

namespace WHMCS\Module\Registrar\RTCustomRegistrar;

use WHMCS\User\Client;
use WHMCS\Database\Capsule as DB;

class RTCore
{
    public static function insert($tableName, $data)
    {
        return DB::table($tableName)->insert($data);
    }

    public static function update($tableName, $where, $data)
    {
        return DB::table($tableName)->where($where)->update($data);
    }

    public static function select($tableName, $select, $where)
    {
        return DB::table($tableName)->select($select)->where($where)->get();
    }

    public static function first($tableName, $where)
    {
        return DB::table($tableName)->where($where)->first();
    }

    public static function value($tableName, $where, $column)
    {
        return DB::table($tableName)->where($where)->value($column);
    }

    public static function exists($tableName, $where)
    {
        return DB::table($tableName)->where($where)->exists();
    }

    public static function delete($tableName, $where)
    {
        return DB::table($tableName)->where($where)->delete();
    }

    private static function sendEmail($type, $data)
    {
        $postData = [
            'messagename' => $type,
            'mergefields' => $data,
        ];
        return localAPI('SendAdminEmail', $postData);
    }

    public static function registerDomain($params)
    {
        try {
            $enableDnsManagement = (bool) $params['dnsmanagement'] ? 'Enable' : 'Disable';
            $enableEmailForwarding = (bool) $params['emailforwarding'] ? 'Enable' : 'Disable';
            $enableIdProtection = (bool) $params['idprotection'] ? 'Enable' : 'Disable';

            $contactDetails = self::prepareContactDetails($params);
            $nameserverData = self::prepareNameservers($params);

            $data = [
                'client_id' => $params['client_id'],
                'client_name' => $params['fullname'],
                'domain' => $params['domain'],
                'nameservers' => $nameserverData,
                'contactdetails' => $contactDetails,
                'dnsmanagement' => $enableDnsManagement,
                'emailforwarding' => $enableEmailForwarding,
                'idprotection' => $enableIdProtection
            ];

            if (!self::exists('mod_RTCustomRegistrar', ['domainid' => $params['domainid']])) {
                self::insert(
                    'mod_RTCustomRegistrar',
                    [
                        'domainid' => $params['domainid'],
                        'nameserver_data' => self::prepareDatabaseData($nameserverData),
                        'contact_data' => self::prepareDatabaseData($contactDetails)
                    ]
                );
            } else {
                self::update(
                    'mod_RTCustomRegistrar',
                    [
                        'domainid' => $params['domainid']
                    ],
                    [
                        'nameserver_data' => self::prepareDatabaseData($nameserverData),
                        'contact_data' => self::prepareDatabaseData($contactDetails)
                    ]
                );
            }

            $emailResponse = self::sendEmail('RTCustomRegistrar Domain Registration', $data);

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function transferDomain($params)
    {
        try {
            $enableDnsManagement = (bool) $params['dnsmanagement'] ? 'Enable' : 'Disable';
            $enableEmailForwarding = (bool) $params['emailforwarding'] ? 'Enable' : 'Disable';
            $enableIdProtection = (bool) $params['idprotection'] ? 'Enable' : 'Disable';

            $nameserverData = self::prepareNameservers($params);

            $data = [
                'client_id' => $params['client_id'],
                'client_name' => $params['fullname'],
                'domain' => $params['domain'],
                'nameservers' => $nameserverData,
                'dnsmanagement' => $enableDnsManagement,
                'emailforwarding' => $enableEmailForwarding,
                'idprotection' => $enableIdProtection,
                'epp_code'      => $params['eppcode']
            ];

            $emailResponse = self::sendEmail('RTCustomRegistrar Domain Transfer', $data);

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function getNameservers($params)
    {
        try {
            $response = self::first('mod_RTCustomRegistrar', ['domainid' => $params['domainid']]);
            return json_decode($response->nameserver_data, true);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function saveNameservers($params)
    {

        try {

            $client = Client::find($params['userid']);
            $nameserverData = self::prepareNameservers($params);

            $data = [
                'client_id' => $client->id,
                'client_name' => $client->firstname . ' ' . $client->lastname,
                'domain'        => $params['domain'],
                'nameservers'   => $nameserverData
            ];

            if (!self::exists('mod_RTCustomRegistrar', ['domainid' => $params['domainid']])) {
                self::insert(
                    'mod_RTCustomRegistrar',
                    [
                        'domainid' => $params['domainid'],
                        'nameserver_data' => self::prepareDatabaseData($nameserverData)
                    ]
                );
            } else {
                self::update(
                    'mod_RTCustomRegistrar',
                    [
                        'domainid' => $params['domainid']
                    ],
                    [
                        'nameserver_data' => self::prepareDatabaseData($nameserverData)
                    ]
                );
            }

            $emailResponse = self::sendEmail('RTCustomRegistrar Domain Nameservers Update', $data);

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function getContactDetails($params)
    {
        try {
            $response = self::first('mod_RTCustomRegistrar', ['domainid' => $params['domainid']]);
            return json_decode($response->contact_data, true);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    public static function saveContactDetails($params)
    {

        try {
            $contactDetails = self::prepareContactDetails($params);
            $data = [
                'client_id' => $params['client_id'],
                'client_name' => $params['fullname'],
                'domain'            => $params['domain'],
                'contactdetails'    => $contactDetails
            ];

            if (!self::exists('mod_RTCustomRegistrar', ['domainid' => $params['domainid']])) {
                self::insert(
                    'mod_RTCustomRegistrar',
                    [
                        'domainid' => $params['domainid'],
                        'contact_data' => self::prepareDatabaseData($contactDetails)
                    ]
                );
            } else {
                self::update(
                    'mod_RTCustomRegistrar',
                    [
                        'domainid' => $params['domainid']
                    ],
                    [
                        'contact_data' => self::prepareDatabaseData($contactDetails)
                    ]
                );
            }

            $emailResponse = self::sendEmail('RTCustomRegistrar Domain ContactDetails Update', $data);

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    public static function getDNS($params)
    {
        try {
            $response = self::first('mod_RTCustomRegistrar', ['domainid' => $params['domainid']]);
            return json_decode($response->dns_data, true);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }


    public static function saveDNS($params)
    {

        try {
            $client = Client::find($params['userid']);

            $dnsArray = array_filter($params['dnsrecords'], function ($item) {
                return !empty($item['hostname']);
            });

            $data = [
                'client_id' => $client->id,
                'client_name' => $client->firstname . ' ' . $client->lastname,
                'domain'            => $params['domain'],
                'dnsrecords'        => $params['dnsrecords']
            ];

            if (!self::exists('mod_RTCustomRegistrar', ['domainid' => $params['domainid']])) {
                self::insert(
                    'mod_RTCustomRegistrar',
                    [
                        'domainid' => $params['domainid'],
                        'dns_data' => self::prepareDatabaseData($dnsArray)
                    ]
                );
            } else {
                self::update(
                    'mod_RTCustomRegistrar',
                    [
                        'domainid' => $params['domainid']
                    ],
                    [
                        'dns_data' => self::prepareDatabaseData($dnsArray)
                    ]
                );
            }

            $emailResponse = self::sendEmail('RTCustomRegistrar Domain DNS Update', $data);

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public static function saveIDProtect($params)
    {
        try {

            $client = Client::find($params['userid']);

            $enableIdProtection = (bool) $params['protectenable'] ? 'Enable' : 'Disable';

            $data = [
                'client_id' => $client->id,
                'client_name' => $client->firstname . ' ' . $client->lastname,
                'domain'        => $params['domain'],
                'idprotection'        => $enableIdProtection
            ];

            $emailResponse = self::sendEmail('RTCustomRegistrar Domain ID Protection', $data);
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private static function prepareDatabaseData($data)
    {
        return json_encode(array_filter($data));
    }

    private static function prepareContactDetails($params)
    {
        $contactTypes = ['Registrant', 'Technical', 'Billing', 'Admin'];
        $contactDetails = [];

        foreach ($contactTypes as $type) {
            $contactDetails[$type] = [
                'First Name' => $params["adminfirstname"],
                'Last Name' => $params["adminlastname"],
                'Company Name' => $params["admincompanyname"],
                'Email Address' => $params["adminemail"],
                'Address 1' => $params["adminaddress1"],
                'Address 2' => $params["adminaddress2"],
                'City' => $params["admincity"],
                'State' => $params["adminfullstate"],
                'Postcode' => $params["adminpostcode"],
                'Country' => $params["admincountry"],
                'Phone Number' => $params["adminfullphonenumber"],
                'Fax Number' => null,
            ];
        }

        return $contactDetails;
    }

    private static function prepareNameservers($params)
    {
        $nameservers = [];

        for ($i = 1; $i <= 5; $i++) {
            $nsKey = "ns$i";
            $nsValue = "ns$i";
            if (!empty($params[$nsKey])) {
                $nameservers[$nsValue] = $params[$nsKey];
            }
        }

        return $nameservers;
    }
}
