<?php

namespace Cycoslave\Proxmox;

use Cycoslave\Proxmox\Helpers\ResponseHelper;

class ProxmoxPools
{
    public function __construct(protected ProxmoxAccess $client) {}

    /**
     * Read system log
     * @throws \Exception
     */
    public function pools()
    {
        $response = $this->client->get('pools');

        if (!isset($response['data'])){
            return ResponseHelper::generate(false,'Pools system log fail.');
        }
        return ResponseHelper::generate(true,'Pools system log.', $response['data']);
    }

    /**
     * pools Id system log
     * @param string $poolid
     * @throws \Exception
     */
    public function poolsId($poolid)
    {
        $response = $this->client->get("pools/$poolid");

        if (!isset($response['data'])){
            return ResponseHelper::generate(false,'Pools system log fail.');
        }
        return ResponseHelper::generate(true,'Pools system log.', $response['data']);
    }

    /**
     * Read system log
     * @param string $poolid
     * @throws \Exception
     */
    public function putPool($poolid, $data = array())
    {
        $response = $this->client->put("pools/{$poolid}", $data);

        if (!isset($response['data'])){
            return ResponseHelper::generate(false,'Pools system log fail.');
        }
        return ResponseHelper::generate(true,'Pools system log.', $response['data']);
    }
}
