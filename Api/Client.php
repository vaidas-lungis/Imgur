<?php

namespace Box\Mod\Imgur\Api;

class Admin extends \Api_Abstract
{

    public function uploadImage($data)
    {
        $data['client_id'] = $this->getIdentity()->id;
        $iamgeRemoteId     = $this->getService()->uploadImage($data);
        return $this->getService()->saveImageRemoteId($iamgeRemoteId);
    }
}