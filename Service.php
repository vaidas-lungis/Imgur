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
        if ($this->di['request']->hasFiles() == 0){
            throw new \Box_Exception('Image is missing', null, 997);
        }

        $extensionService = $this->di['mod_service']('Extension');
        $config = $extensionService->getConfig('mod_imgur');

        if (!$this->isSetClientId($config)){
            throw new \Box_Exception('Client ID param is missing');
        }


        $files = $this->di['request']->getUploadedFiles();
        $file = $files[0];
        $params = array(
            'type'  => 'base64',
            'image' => base64_encode($this->di['tools']->file_get_contents($file->getRealPath())),
        );

        $client = $this->di['guzzle_client'];
        $headers = array('Authorization' => sprintf('Client-ID %s', $config['client_id']));

        $result = $client->post('https://api.imgur.com/3/image.json', $headers, $params)
            ->send()
            ->json();
        return isset($result['data']['link']) ? $result['data']['link'] : '';
    }

    public function saveImageInfo($imageLink, $data = array())
    {
        $model = $this->di['db']->dispense('imgur');
        $model->client_id = $data['client_id'];
        $model->support_ticket_id = $data['support_ticket_id'];
        $model->url = $imageLink;
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