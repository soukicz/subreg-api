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
        if(!$this->client) {
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
        $this->getClient();
        $data['ssid'] = $this->key;

        $res = $this->getClient()->__call($command, ['data' => $data]);

        if($res['status'] != 'ok') {
            throw new IOException('Subreg: ' . $res['error']['errormsg']);
        }

        return $res['data'];
    }

    /**
     * @param string $ownerId
     * @param string $name
     * @param string[] $hosts
     */
    public function createNameServerSet($ownerId, $name, array $hosts) {
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
                ->setExpiration(new \DateTime($response['expire']));
        }

        return $list;
    }

    /**
     * @param string $name
     * @param int $period
     */
    public function renewDomain($name, $period = 1) {
        $this->send('Make_Order', [
            'order' => [
                'domain' => $name,
                'type' => 'Renew_Domain',
                'params' => [
                    'period' => $period
                ]
            ]
        ]);
    }
}
