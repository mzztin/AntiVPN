<?php

namespace Martin\AntiVPN\task;

use Martin\AntiVPN\AntiVPN;
use mysql_xdevapi\Exception;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class CheckAsyncTask extends AsyncTask
{
    /** @var string */
    private $name;

    /** @var string */
    private $ip;

    public function __construct(string $name, string $ip)
    {
        $this->setName($name);
        $this->setIp($ip);
    }

    public function onRun()
    {
        $url = "http://check.getipintel.net/check.php?ip=" . $this->getIp() . "&format=json&contact=test@outlook.de";
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
        ]);
        $result = curl_exec($curl);
        $data = json_decode($result, true);

        $this->setResult(array(
            "name" => (string) $this->getName(),
            "result" => $data["result"]
        ));
    }


    /**
     * @param Server $server
     * @throws Exception
     */
    public function onCompletion(Server $server)
    {
        print_r($this->getResult());
        $result = (float) $this->getResult()["result"];
        $name = $this->getResult()["name"];

        if ($result !== null) {
            if ($result > 0.98) {
                $player = $server->getPlayerExact($name);
                $player->kick(AntiVPN::getInstance()->getCfg()->get("kick-message"), false);
            }
        } else {
            new Exception("Could not fetch data!");
        }
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}