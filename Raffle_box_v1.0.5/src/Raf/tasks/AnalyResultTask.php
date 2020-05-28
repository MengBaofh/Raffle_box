<?php
/**
 * MB系列
 * 插件开源
 * 作者:梦宝(fanghao)
 * */

namespace Raf\tasks;



use pocketmine\command\ConsoleCommandSender;
use pocketmine\inventory\PlayerInventory;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use Raf\Main;
use pocketmine\utils\Config;

class AnalyResultTask extends Task
{
    private $ingame;
    private $id;
    private $instance;
    private $result;
    private $oldItems = [];
    private $player;
    private $inv;
    private $n = [];
    private $lx;
    private $jb;
    private $gl;
    private $zl;
    public function __construct(Main $instance,Player $player,int $id,PlayerInventory $inv,array $olditems,array $n,$lx,$jb,$gl,$zl,$ingame,bool $result)
    {
        $this->ingame = $ingame;
        $this->instance = $instance;
        $this->player = $player;
        $this->id = $id;
        $this->inv = $inv;
        $this->oldItems = $olditems;
        $this->n = $n;
        $this->lx = $lx;
        $this->jb = $jb;
        $this->gl = $gl;
        $this->zl = $zl;
        $this->result = $result;
    }

    /**
     * Actions to execute when run
     *
     * @param int $currentTick
     *
     * @return void
     */
    public function onRun(int $currentTick)
    {
        $this->instance->getScheduler()->cancelTask($this->id);
        //还原背包
        for($i=0;$i<count($this->oldItems);$i++)
        {
            $this->inv->setItem($i,$this->oldItems[$i]);
        }
            //判断是否中奖
        if(!$this->result)
        {
           $this->player->sendMessage("§c很遗憾，您未中奖。");
           $name=$this->player->getName();
           $this->ingame->set("$name","false");
           $this->ingame->save();
           return true;
        }else{
           //全服公告玩家，中奖内容，奖励级别,奖励类型
           Server::getInstance()->broadcastMessage("§c恭喜§b玩家.{$this->player->getName()}.§e抽中{$this->jb[0]}§e级大奖{$this->n[0]}。");
           //发送玩家中奖信息
           $this->player->sendMessage("§e恭喜您中奖:{$this->jb[0]}级大奖{$this->n[0]}");
           //执行指令
           switch($this->lx[0])
           {
               case "money":
               case "item":
               case "exp":
               //字符转义
               $p = $this->player->getName();
               $zf = array(
                           "{player}",
                           "*"
                          );
               $zfs = array(
                           "{$p}",
                           " "
                          );
               $Comrep=str_replace($zf,$zfs,$this->zl[0]);
               //后台执行指令$Comrep
               Server::getInstance()->dispatchCommand(new ConsoleCommandSender(),$Comrep);
               break;
           }
           $name=$this->player->getName();
           $this->ingame->set("$name","false");
           $this->ingame->save();
           return true;
        }
    }
}
