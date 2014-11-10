<?php

namespace Box\Mod\Imgur\Api;

class Admin extends \Api_Abstract
{

    public function config_get($data)
    {
        return $this->getService()->getConfig($data);
    }

    public function config_update($data)
    {
        return $this->getService()->updateConfig($data);
    }

}