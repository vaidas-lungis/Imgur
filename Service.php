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
}