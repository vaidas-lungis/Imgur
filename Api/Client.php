<?php

namespace Box\Mod\Imgur\Api;

class Admin extends \Api_Abstract
{

    public function uploadImage($data)
    {
        $required = array(
            'support_ticket_id' => 'Ticket ID is missing',
        );
        $this->di['validator']->checkRequiredParamsForArray($required, $data);

        $data['client_id'] = $this->getIdentity()->id;
        $imageLink = $this->getService()->uploadImage($data);
        if (empty($imageLink)){
            throw new \Box_Exception('Upload unsuccessful');
        }
        return $this->getService()->saveImageInfo($imageLink, $data);
    }
}