<?php
namespace Soukicz\SubregApi;

class Client {
    protected $login;
    protected $password;
    protected $key;
    /**
     * @var \SoapClient
     */
    protected $client;

    function __construct($login, $password) {
        $this->login = $login;
        $this->password = $password;
    }

    protected function getClient() {
        if(!$this->key) {
            $this->client = new \SoapClient(null, [
                'location' => 'https://soap.subreg.cz/cmd.php',
                'uri' => 'https://soap.subreg.cz/soap'
            ]);

            $res = $this->client->__call('Login', [
                'data' => [
                    'login' => $this->login,
                    'password' => $this->password,
                ]
            ]);

            if($res['status'] != 'ok') {
                throw new IOException('Subreg: ' . $res['error']['errormsg']);
            }
            $this->key = $res['data']['ssid'];
        }

        return $this->client;
    }

    protected function send($command, array $data) {
        $data['ssid'] = $this->key;

        $params = ['data' => $data];
        $res = $this->getClient()->__call($command, $params);

        if($res['status'] != 'ok') {
            throw new IOException('Subreg: ' . $res['error']['errormsg']);
        }

        return $res['data'];
    }

    public function createNameServerSet($ownerId, $name, $hosts) {
        $nss = [];
        foreach ($hosts as $n) {
            $nss[]['hostname'] = $n;
        }
        $this->createObject($name, [
            'type' => 'nsset',
            'params' => [
                'tech' => [
                    'id' => $ownerId
                ],
                'hosts' => $hosts
            ]
        ]);
    }

    public function createObject($name, array $params) {
        $this->send('Make_Order', [
            'order' => [
                'type' => 'Create_Object',
                'object' => $name,
                'params' => $params,
            ]
        ]);
    }

    /**
     * @return DomainInfo[];
     */
    public function getDomains() {
        $list = [];
        foreach ($this->send('Domains_List', [])['domains'] as $response) {
            $list[] = (new DomainInfo())
                ->setName($response['name'])
                ->setExpiration(new \DateTime($response['expiration']));
        }

        return $list;
    }
}
