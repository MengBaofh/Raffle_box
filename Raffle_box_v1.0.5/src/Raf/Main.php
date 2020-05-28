<?php

/**
 * MB系列
 * 插件开源
 * 作者:梦宝(fanghao)
 * */

namespace Raf;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use onebone\economyapi\EconomyAPI;

use Raf\tasks\AnalyResultTask;
use Raf\tasks\RunItemTask;

/**
 * Plug-in open source 
 * Reselling is prohibited 
 */

class Main extends PluginBase implements Listener
{

    public $qz = "§e[§d抽奖箱§b插件§e]§f";
    //玩家抽奖ing铺垫
    //    public $run = [];
    public $set = false;
    public $del = false;

    public function onLoad()
    {
        $this->getLogger()->info("抽奖箱插件加载中...");
    } //ok
    public function onEnable()
    {
        @mkdir($this->getDataFolder(), 0777, true);
        @mkdir($this->getDataFolder() . "Boxes/");
        $this->cjx1 = new Config($this->getDataFolder() . "Boxes/" . "x&y&z&level.yml", Config::YAML, array(
            "use" => "1",
            "抽奖消耗类型" => "item",
            "消耗数量" => "10",
            "消耗物品ID" => "264:0"
        ));
        @mkdir($this->getDataFolder() . "ItemSet/");
        $this->cjpz1 = new Config($this->getDataFolder() . "ItemSet/" . "1.yml", Config::YAML, array(
            "1" => "§a金币大奖 money C 0.9 givemoney*{player}*100",
            "2" => "§e经验大奖 exp B 0.5 exp*{player}*15",
            "8" => "§b钻石奖励 item A 0.1 give*{player}*264:0*1"
        ));
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    public function onDisable()
    {
        $this->getLogger()->info("§c抽奖箱插件关闭.");
    } //ok
    //////////////////////////分割线/////////////////////////
    //    public function onPlayerJoin(PlayerJoinEvent $event)
    //    {//初始化懒得改了>_<反正之前也没错
    //        $player=$event->getPlayer();
    //        if(!isset($this->run[$player->getName()]) or
    //           $this->run[$player->getName()]==true)
    //        {
    //            $this->run=false;
    //        }
    //    }//ok
    //////////////////////////分割线/////////////////////////
    public function saveBoxF(BlockBreakEvent $block): void //___抽奖箱保护1
    {
        $x = $block->getBlock()->getX();
        $y = $block->getBlock()->getY();
        $z = $block->getBlock()->getZ();
        $qz = "§e[§d抽奖箱§b插件§e]§f";
        $file_search = $this->searchFile("Boxes");
        $xx = [];
        $yy = [];
        $zz = [];
        foreach ($file_search as $f) {
            $xx[] = explode("&", $f)[0];
            $yy[] = explode("&", $f)[1];
            $zz[] = explode("&", $f)[2];
        }
        if (
            in_array($x, $xx) and
            in_array($y, $yy) and
            in_array($z, $zz)
        ) {
            $block->getPlayer()->sendMessage("{$qz}§c禁止破坏抽奖箱!");
            $block->setCancelled();
        }
    } //ok
    public function saveBoxS(PlayerInteractEvent $block): void //___抽奖箱保护2
    {
        $x = $block->getBlock()->getX();
        $y = $block->getBlock()->getY();
        $z = $block->getBlock()->getZ();
        $qz = "§e[§d抽奖箱§b插件§e]§f";
        $file_search = $this->searchFile("Boxes");
        $xx = [];
        $yy = [];
        $zz = [];
        foreach ($file_search as $f) {
            $xx[] = explode("&", $f)[0];
            $yy[] = explode("&", $f)[1];
            $zz[] = explode("&", $f)[2];
        } //循环获取并存入数组
        if (
            in_array($x, $xx) and
            in_array($y, $yy) and
            in_array($z, $zz)
        ) {
            $block->setCancelled();
        }
    } //ok
    public function onSetRaffBox(PlayerInteractEvent $event) //___设置抽奖箱
    {
        $block = $event->getBlock();
        $set = $this->set;
        if ($set) {
            $player = $event->getPlayer();
            $qz = "§e[§d抽奖箱§b插件§e]§f";
            $id = $block->getID();
            $ts = $block->getDamage(); //特殊值
            $isBox = $this->isBox($id, $ts); //判断是否为箱子
            if (!$isBox) {
                $player->sendMessage("{$qz}§c请点击箱子!");
            } else {
                $x = $block->getX();
                $y = $block->getY();
                $z = $block->getZ();
                $level = $block->level->getFolderName();
                @mkdir($this->getDataFolder() . "Boxes/");
                if (!file_exists($this->getDataFolder() . "Boxes/" . "$x&$y&$z&$level.yml")) {
                    $cjx = new Config($this->getDataFolder() . "Boxes/" . "$x&$y&$z&$level.yml", Config::YAML, array(
                        "use" => "",
                        "抽奖消耗类型" => "",
                        "消耗数量" => "",
                        "消耗物品ID" => "",
                    ));
                    $cjx->save();
                    $player->sendMessage("{$qz}§e成功设置一个抽奖箱，请前往配置文件编写内容(请一定按模板写)。");
                    $player->sendMessage("{$qz}§a已自动帮您获取配置文件位置:Raff_Box\Boxes\ $x&$y&$z&$level.yml");
                    $player->sendMessage("{$qz}§c请输入/R change来恢复您的抽奖权限!!!!!");
                    $this->quit();
                } else {
                    $player->sendMessage("{$qz}§c抽奖箱已存在");
                }
            }
        }
    } //ok
    public function onDelRaffBox(PlayerInteractEvent $event) //___删除抽奖箱
    {
        if ($this->del) {
            $player = $event->getPlayer();
            $qz = "§e[§d抽奖箱§b插件§e]§f";
            $block = $event->getBlock();
            $id = $block->getID();
            $ts = $block->getDamage(); //特殊值
            $isBox = $this->isBox($id, $ts); //判断是否为箱子
            if (!$isBox) {
                $player->sendMessage("{$qz}§c请点击箱子!");
                $event->setCancelled();
            } else {
                $x = $block->getX();
                $y = $block->getY();
                $z = $block->getZ();
                $level = $block->level->getFolderName();
                @mkdir($this->getDataFolder() . "Boxes/");
                if (!file_exists($this->getDataFolder() . "Boxes/" . "$x&$y&$z&$level.yml")) {
                    $player->sendMessage("{$qz}§c这不是抽奖箱。");
                } else {
                    unlink($this->getDataFolder() . "Boxes/" . "$x&$y&$z&$level.yml");
                    $player->sendMessage("{$qz}§e成功删除一个抽奖箱。");
                    $this->quit();
                }
            }
        }
    } //ok
    public function isBox($id, $ts) //__判断点击的是否为箱子
    {
        $ids = "54,130,146";
        $idsr = explode(",", $ids);
        if (!in_array($id, $idsr)) {
            return false;
        } else {
            return true;
        }
    } //ok
    public function startRaff(Player $player, $x, $y, $z, $level, $playerna) //__玩家开始抽奖
    {
        $qz = "§e[§d抽奖箱§b插件§e]§f";
        ///生成随机数
        $ron = mt_rand(1, 100);
        ///获取奖励内容
        ///修饰配置文件$pzwj1
        $file_search = $this->searchFile("ItemSet");
        $file_searchs = $this->searchFile("Boxes");
        $us = new Config($this->getDataFolder() . "Boxes/" . "$x&$y&$z&$level.yml", Config::YAML, array());
        $use = $us->get("use");
        $ingame = new Config($this->getDataFolder() . "run.yml", Config::YAML, array());
        if (!$use) {
            $player->sendMessage("§c该抽奖箱配置文件未设置!!!!!!");
            $ingame->set("$playerna", "false");
            $ingame->save();
            return true;
        }
        $player->addTitle("§e开始抽奖中");
        $f = [];
        foreach ($file_search as $file) {
            $f[] = explode(".", $file)[0]; //1.yml
        }
        if (in_array($use, $f)) {
            $pzwj0 = new Config($this->getDataFolder() . "ItemSet/" . "$use.yml", Config::YAML, array());
            //$pzwjj = $pzwj0->getAll();
            $pzwj2 = $pzwj0->get("$ron");
            if ("$pzwj2" == null) return $this->startRaff($player, $x, $y, $z, $level, $playerna);
            //实现随机奖励第一重
            $n[] = explode(" ", $pzwj2)[0];
            $lx[] = explode(" ", $pzwj2)[1];
            $jb[] = explode(" ", $pzwj2)[2];
            $gl[] = explode(" ", $pzwj2)[3];
            $zl[] = explode(" ", $pzwj2)[4];
            $result = $this->isWin($player, $gl);
            //保存背包$inv
            $inv = $player->getInventory();
            $olditems = array();
            for ($i = 0; $i < $inv->getSize(); $i++) {
                $olditems[] = $inv->getItem($i);
            }
            $task = $this->getScheduler()->scheduleRepeatingTask(new RunItemTask($player, $i), 2);
            $id = $task->getTaskId();
            $this->getScheduler()->scheduleDelayedTask(new AnalyResultTask($this, $player, $id, $inv, $olditems, $n, $lx, $jb, $gl, $zl, $ingame, $result), 30);
            return true;
        }
    }
    public function isWin($player, $gl) //__实现随机奖励第二重
    {
        //生成参照随机数
        $czron = mt_rand(0, 100);
        //换算概率
        $hsgl = (int) ($gl[0] * 100);
        //判断
        if ($hsgl > $czron) {
            //中奖
            return true;
        } else {
            //未中奖
            return false;
        }
    }
    public function onBlockBox(PlayerInteractEvent $event) //__玩家点击抽奖箱(不能是普通箱子涩)开始抽奖
    {
        $qz = "§e[§d抽奖箱§b插件§e]§f";
        $player = $event->getPlayer();
        $playerna = $player->getName();
        $ingame = new Config($this->getDataFolder() . "run.yml", Config::YAML, array());
        $gana = $ingame->get("$playerna");
        if ("$gana" == "true") return $player->sendMessage("{$qz}§c您正在抽奖中...");
        $block = $event->getBlock();
        $x = $block->x;
        $y = $block->y;
        $z = $block->z;
        $level = $block->level->getFolderName();
        $id = $block->getID();
        $ts = $block->getDamage();
        $isBox = $this->isBox($id, $ts); //判断是否点击的是箱子
        if ($isBox) {
            $file_search = $this->searchFile("Boxes");
            foreach ($file_search as $f) {
                $xyz = "$x&$y&$z&$level";
                $result[] = explode(".", $f)[0];
            } //存入数组
            if (in_array($xyz, $result)) {
                //判断是否足够物品
                if (!$this->DecreaseThing($player, $x, $y, $z, $level)) return $player->sendMessage("{$qz}§c你没有足够的抽奖物品");
                //玩家抽奖ing
                $ingame->set("$playerna", "true");
                $ingame->save();
                $this->startRaff($player, $x, $y, $z, $level, $playerna);
            }
        }
    } //ok
    public function DecreaseThing(Player $player, $x, $y, $z, $level): bool //__抽奖消耗///////////抽奖箱
    {
        $qz = "§e[§d抽奖箱§b插件§e]§f";
        $cjx = new Config($this->getDataFolder() . "Boxes/" . "$x&$y&$z&$level.yml", Config::YAML, array());
        $chlx = $cjx->get("抽奖消耗类型");
        $chsl = $cjx->get("消耗数量");
        $chwp1 = $cjx->get("消耗物品ID");
        switch ($chlx) { //判断抽奖类型
            case "money":
                if (EconomyAPI::getInstance()->myMoney($player) < (int) $chsl) return false;
                EconomyAPI::getInstance()->reduceMoney($player, (int) $chsl);
                return true;
                break;
            case "item":
                $id = explode(":", $chwp1)[0];
                $ts = explode(":", $chwp1)[1];
                $item = Item::get($id, $ts, $chsl);
                if ($player->getInventory()->contains($item)) {
                    $array[] = explode(",", $item)[0];
                } else {
                    $array[] = 1;
                }
                if (in_array(1, $array)) {
                    return false;
                } else {
                    $player->getInventory()->removeItem($item);
                    return true;
                }
                break;
        }
        //返回true表示有足够物品，false则没有
        return true;
    }
    public function searchFile(String $path): array
    {
        $files = array();
        $dir = $this->getDataFolder() . "/" . $path . "/";

        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                while (($file = readdir($handle)) !== false) {
                    if ($file != "." && $file != "..") {
                        $files[] = $file;
                    }
                }
                closedir($handle);
                return $files;
            }
        }
        return []; //返回文件名n.yml
    } //ok
    public function quit()
    {
        $this->set = false;
        $this->del = false;
    } //ok
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $arg): bool
    {
        $qz = "§e[§d抽奖箱§b插件§e]§f";
        $name = $sender->getName();
        switch ($cmd->getName()) {
            case "R":
                if (!isset($arg[0])) {
                    $sender->sendMessage("{$qz}§c输入/R help查看帮助");
                    return true;
                } 
                switch ($arg[0]) {
                    case "help":
                        $sender->sendMessage("§e/R set---设置一个抽奖箱。\n§e/R del---删除一个抽奖箱。\n§e/R quit---注销设置(不会删除配置文件)\n§e/R change---恢复抽奖权限");
                        return true;
                        break; ///R help完///
                    case "set":
                        if (!$sender instanceof Player) {
                            $sender->sendMessage("{$qz}§c请在游戏中使用");
                            return true;
                        } else {
                            $sender->sendMessage("{$qz}§e点击一个箱子来设置抽奖箱。");
                            $this->set = true;
                        }
                        break; ///R set完///pb=>onSetRaffBo
                    case "del":
                        if (!$sender instanceof Player) {
                            $sender->sendMessage("{$qz}§c请在游戏中使用");
                            return true;
                        } else {
                            $sender->sendMessage("{$qz}§a点击箱子来删除抽奖箱。");
                            $this->del = true;
                        }
                        break; ///R del完///pb=>onDelRaffBox
                    case "quit":
                        if (!$sender instanceof Player) {
                            $sender->sendMessage("{$qz}§c请在游戏中使用");
                            return true;
                        } else {
                            $sender->sendMessage("{$qz}§e已重置设置");
                            $this->quit();
                        }
                        break; ///R quit完
                    case "change":
                        if (!$sender instanceof Player) {
                            $sender->sendMessage("{$qz}§c请在游戏中使用");
                            return true;
                        } else {
                            $sender->sendMessage("{$qz}§e已恢复您的抽奖权限");
                            $c = new Config($this->getDataFolder() . "run.yml", Config::YAML, array());
                            $c->set("$name", "false");
                            $c->save();
                        }
                        break; ///R change完
                } 
                return true;
        } 
    } 
}
/**
 * 插件开源，禁止倒卖
 * 転売禁止
 * プラグインオープンソース
 * 作者:梦宝
 * 有任何问题请加QQ询问:825585398
 * */
