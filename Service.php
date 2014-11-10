<?php

namespace Box\Mod\Imgur;

use Box\InjectionAwareInterface;

class Service implements InjectionAwareInterface
{
    /**
     * @var \Box_Di
     */
    protected $di = null;

    /**
     * @param \Box_Di $di
     */
    public function setDi($di)
    {
        $this->di = $di;
    }

    /**
     * @return \Box_Di
     */
    public function getDi()
    {
        return $this->di;
    }

    public function install()
    {
        $sql="
        CREATE TABLE IF NOT EXISTS `imgur` (
        `id` bigint(20) NOT NULL AUTO_INCREMENT,
        `client_id` bigint(20) DEFAULT NULL,
        `support_ticket_id` bigint(20) DEFAULT NULL,
        `url` VARCHAR (255) DEFAULT NULL,
        `created_at` datetime DEFAULT NULL,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `client_id_idx` (`client_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
        ";

        return $this->di['db']->exec($sql) > 0;
    }

    public function uninstall()
    {
        $this->di['db']->exec('DELETE imgur WHERE extension = "mod_imgur"');

        return $this->di['db']->exec("DROP TABLE IF EXISTS `imgur`") > 0;
    }

    public function getConfig()
    {
        $extensionService = $this->di['mod_service']('Extension');
        return $extensionService->getConfig('mod_imgur');
    }

    public function updateConfig($data = array())
    {
        if (!$this->isSetClientId($data)){
            throw new \Box_Exception('Client ID param is missing');
        }

        $extensionService = $this->di['mod_service']('Extension');
        $data['ext'] = 'mod_imgur';
        return $extensionService->setConfig($data);
    }

    public function uploadImage($data)
    {
        if (!isset($data['image'])){
            throw new \Box_Exception('Image is missing');
        }

        $extensionService = $this->di['mod_service']('Extension');
        $config = $extensionService->getConfig('mod_imgur');

        if (!$this->isSetClientId($config)){
            throw new \Box_Exception('Client ID param is missing');
        }

        $headers = sprintf('Authorization: Client-ID %s', $config['client_id']);
        $params = array(
            'type'  => 'base64',
            'image' => base64_encode($this->di['tools']->file_get_contents($data['image'])),
        );

        $client = $this->di['guzzle_client'];
        return $client->post('https://api.imgur.com/3/image', $headers, $params);
    }

    public function saveImageRemoteId($image_id)
    {
        $model = $this->di['db']->dispense('Imgur');
        $model->url = sprintf('https://api.imgur.com/3/image/%d', $image_id);
        $model->created_at = date('c');
        $model->updated_at = date('c');
        $id = $this->di['db']->store($model);
        return $id;
    }

    public function isSetClientId($config = array())
    {
        return isset($config['client_id']) && strlen(trim($config['client_id'])) > 0;
    }
}