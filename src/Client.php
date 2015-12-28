<?php
namespace Simplia\SubregApi;

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

    public function connect() {
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
            $this->key = $res['ssid'];
        }
    }

    protected function send($command, array $data) {
        $data['ssid'] = $this->key;

        $params = ['data' => $data];
        $res = $this->client->__call($command, $params);

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
}
