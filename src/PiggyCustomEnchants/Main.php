<?php

namespace PiggyCustomEnchants;

use PiggyCustomEnchants\Commands\CustomEnchantCommand;
use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

/**
 * Class Main
 * @package PiggyCustomEnchants
 */
class Main extends PluginBase
{
    public $vampirecd;
    public $cloakingcd;
    public $berserkercd;

    public function onEnable()
    {
        CustomEnchants::init();
        $this->getServer()->getCommandMap()->register("customenchant", new CustomEnchantCommand("customenchant", $this));
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getLogger()->info("§aEnabled");
    }

    /**
     * @param Item $item
     * @param $id
     * @return null|CustomEnchants
     */
    public function getEnchantment(Item $item, $id)
    {
        if (!$item->hasEnchantments()) {
            return null;
        }
        foreach ($item->getNamedTag()->ench as $entry) {
            if ($entry["id"] === $id) {
                $e = CustomEnchants::getEnchantment($entry["id"]);
                $e->setLevel($entry["lvl"]);
                return $e;
            }
        }
        return null;
    }

    /**
     * @param Item $item
     * @param $ench
     * @param $level
     * @param Player $player
     * @param CommandSender $sender
     * @return bool
     */
    public function addEnchantment(Item $item, $ench, $level, Player $player, CommandSender $sender)
    {
        //TODO: Check if item can get enchant
        $ench = CustomEnchants::getEnchantByName($ench);
        if ($ench == null) {
            $sender->sendMessage("§cInvalid enchantment.");
            return false;
        }
        $ench->setLevel($level);
        if (!$item->hasCompoundTag()) {
            $tag = new CompoundTag("", []);
        } else {
            $tag = $item->getNamedTag();
        }
        if (!isset($tag->ench)) {
            $tag->ench = new ListTag("ench", []);
            $tag->ench->setTagType(NBT::TAG_Compound);
        }
        $found = false;
        foreach ($tag->ench as $k => $entry) {
            if ($entry["id"] === $ench->getId()) {
                $tag->ench->{$k} = new CompoundTag("", [
                    "id" => new ShortTag("id", $ench->getId()),
                    "lvl" => new ShortTag("lvl", $ench->getLevel())
                ]);
                $item->setNamedTag($tag);
                $item->setCustomName(str_replace(TextFormat::GRAY . $ench->getName() . " " . $this->getRomanNumber($entry["lvl"]), TextFormat::GRAY . $ench->getName() . " " . $this->getRomanNumber($ench->getLevel()), $item->getName()));
                $found = true;
                break;
            }
        }
        if (!$found) {
            $tag->ench->{count($tag->ench) + 1} = new CompoundTag($ench->getName(), [
                "id" => new ShortTag("id", $ench->getId()),
                "lvl" => new ShortTag("lvl", $ench->getLevel())
            ]);
            $level = $this->getRomanNumber($ench->getLevel());
            $item->setNamedTag($tag);
            $item->setCustomName($item->getName() . "\n" . TextFormat::GRAY . $ench->getName() . " " . $level);
        }
        $player->getInventory()->setItemInHand($item);
        $sender->sendMessage("§aEnchanting suceeded.");
    }

    /**
     * @param $integer
     * @return string
     */
    public function getRomanNumber($integer) //Thank you @Muqsit!
    {
        $table = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $return = '';
        while ($integer > 0) {
            foreach ($table as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $return .= $rom;
                    break;
                }
            }
        }
        return $return;
    }

    /**
     * @param Item $item
     * @param CustomEnchants $enchant
     * @param $event
     * @return bool
     */
    public function canUse(Item $item, CustomEnchants $enchant, $event = null)
    {
        //TODO: Implement
    }
}