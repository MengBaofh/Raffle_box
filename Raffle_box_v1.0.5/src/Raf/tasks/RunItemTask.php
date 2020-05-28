<?php
/**
 * MB系列
 * 插件开源
 * 作者:梦宝(fanghao)
 * */

namespace Raf\tasks;


use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class RunItemTask extends Task
{

    private $inv;
    private $i;

    public function __construct(Player $player,$i)
    {
        $this->player = $player;
        $this->i = $i;
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
        //随机生成
        $sjwpID=mt_rand(264,266);
        $sjwpC = mt_rand(0,99);
        $sjwp = Item::get($sjwpID,0,$sjwpC);
//        $this->inv->getInventory()->setItem($this->i,$sjwp);
        $inv=$this->player->getInventory();
        $olditems = array();
        for($i=0;$i<$inv->getHotbarSize();$i++){
        $olditems[] = $inv->getItem($i);
        $inv->setItem($i,$sjwp);
  //#item#改成你要改的item
}
    }
}