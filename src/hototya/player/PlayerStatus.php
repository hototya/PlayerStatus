<?php
namespace hototya\player;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\command\{
    Command,
    CommandExecutor,
    CommandSender
};

use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\Player;

class PlayerStatus extends PluginBase implements Listener, CommandExecutor
{
    private $db;

    public function onEnable()
    {
        $this->db = new DataBase($this->getDataFolder() . 'playerdata.db');
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($label === 'ps') {
            if (!isset($args[0])) return false;
            switch ($args[0]) {
                case 'status':
                    if ($sender instanceof Player) {
                        if (isset($args[1])) {
                            $name = strtolower($args[1]);
                        } else {
                            $name = strtolower($sender->getName());
                        }
                        $sender->sendMessage($this->getDataDisplay($name));
                        return true;
                    } else {
                        if (isset($args[1])) {
                            $name = strtolower($args[1]);
                            $sender->sendMessage($this->getDataDisplay($name));
                            return true;
                        } else {
                            return false;
                        }
                    }
                    break;
                case 'comment':
                    if ($sender instanceof Player) {
                        if (isset($args[1])) {
                            if (mb_strlen($args[1]) <= 20) {
                                $name = strtolower($sender->getName());
                                $this->db->setComment($name, $args[1]);
                                $sender->sendMessage('貴方の一言を '.$args[1].' に変更しました。');
                                return true;
                            } else {
                                $sender->sendMessage('20文字以内にして下さい。');
                                return true;
                            }
                        } else {
                            return false;
                        }
                    } else {
                        $sender->sendMessage('ゲーム内で使用して下さい。');
                        return true;
                    }
                    break;
                default:
                    return false;
            }
        }
    }

    public function onLogin(PlayerLoginEvent $event)
    {
        $name = strtolower($event->getPlayer()->getName());
        if ($this->db->getData($name) === null) {
            $this->db->dataRegister($name);
        }
    }

    public function onDeath(PlayerDeathEvent $event)
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $death = $this->db->getDeath($name);
        $this->db->setDeath($name, ++$death);
        $ev = $player->getLastDamageCause();
        if ($ev instanceof EntityDamageByEntityEvent) {
            $damager = $ev->getDamager();
            if ($damager instanceof Player) {
                $damagerName = strtolower($damager->getName());
                $kill = $this->db->getKill($damagerName);
                $this->db->setKill($damagerName, ++$kill);
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event)
    {
        $this->db->updateLastPlayed(strtolower($event->getPlayer()->getName()));
    }

    public function getDataDisplay(string $name): string
    {
        $data = $this->db->getData($name);
        if ($data !== null) {
            $title = '§b§l===== '.$name.'様のステータス =====§r';
            $kill = 'キル数 : '.$data['kill'];
            $death = 'デス数 : '.$data['death'];
            $first = '初プレイ日 : '.$data['first'];
            $last = '最終プレイ日 : '.$data['last'];
            $comment = 'ひとこと : '.$data['comment'];
            $message = $title."\n".$kill."\n".$death."\n".$first."\n".$last."\n".$comment;
        } else {
            $message = $name.'様のデータはありません。';
        }
        return $message;
    }
}
