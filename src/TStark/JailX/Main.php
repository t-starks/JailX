<?php

namespace TStark\JailX;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\Server;

class Main extends PluginBase {

    private $jailPosition;
    private $jailMessage;
    private $unjailMessage;
    private $welcomeMessage;
    private $welcomeSubtitle;
    private $jailedPlayers = [];

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->jailPosition = $this->getJailPositionFromConfig();
        $this->jailMessage = $this->getConfig()->get("jail-message", "§cYou have been sent to jail!");
        $this->unjailMessage = $this->getConfig()->get("unjail-message", "§aYou have been released from jail!");
        $this->welcomeMessage = $this->getConfig()->get("welcome-message", "§eWelcome to jail");
        $this->welcomeSubtitle = $this->getConfig()->get("welcome-subtitle", "§6Good luck during your stay");
        $this->loadJailedPlayers();
    }

    public function onDisable(): void {
        $this->saveJailedPlayers();
    }

    private function loadJailedPlayers(): void {
        $filePath = $this->getDataFolder() . "players.json";
        if (file_exists($filePath)) {
            $this->jailedPlayers = json_decode(file_get_contents($filePath), true);
        }
    }

    private function saveJailedPlayers(): void {
        $filePath = $this->getDataFolder() . "players.json";
        file_put_contents($filePath, json_encode($this->jailedPlayers));
    }

    private function getJailPositionFromConfig(): ?Position {
        $pos = $this->getConfig()->get("jail-position", null);
        if ($pos !== null) {
            $level = $this->getServer()->getWorldManager()->getWorldByName($pos["level"]);
            if ($level !== null) {
                return new Position($pos["x"], $pos["y"], $pos["z"], $level);
            }
        }
        return null;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            return false;
        }

        switch ($command->getName()) {
            case "jail":
                if ($sender->hasPermission("jailx.command.jail")) {
                    if (count($args) === 1) {
                        $target = $this->getServer()->getPlayerByPrefix($args[0]);
                        if ($target instanceof Player) {
                            $this->sendToJail($target);
                            $sender->sendMessage(TextFormat::GREEN . "Player {$target->getName()} was sent to jail.");
                        } else {
                            $sender->sendMessage(TextFormat::RED . "Player {$args[0]} is not online.");
                        }
                    } else {
                        $sender->sendMessage(TextFormat::RED . "Usage: /jail <nick>");
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "You do not have permission to use this command.");
                }
                return true;
            case "setjail":
                if ($sender->hasPermission("jailx.command.setjail")) {
                    $this->jailPosition = $sender->getPosition();
                    $this->getConfig()->set("jail-position", ["x" => $this->jailPosition->getX(), "y" => $this->jailPosition->getY(), "z" => $this->jailPosition->getZ(), "level" => $this->jailPosition->getWorld()->getFolderName()]);
                    $this->getConfig()->save();
                    $sender->sendMessage(TextFormat::GREEN . "Jail position set successfully.");
                } else {
                    $sender->sendMessage(TextFormat::RED . "You do not have permission to use this command.");
                }
                return true;
            case "unjail":
                if ($sender->hasPermission("jailx.command.unjail")) {
                    if (count($args) === 1) {
                        $target = $this->getServer()->getPlayerByPrefix($args[0]);
                        if ($target instanceof Player) {
                            $this->releaseFromJail($target);
                            $sender->sendMessage(TextFormat::GREEN . "Player {$target->getName()} was released from jail.");
                        } else {
                            $sender->sendMessage(TextFormat::RED . "Player {$args[0]} is not online.");
                        }
                    } else {
                        $sender->sendMessage(TextFormat::RED . "Usage: /unjail <nick>");
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "You do not have permission to use this command.");
                }
                return true;
            default:
                return false;
        }
    }

    private function sendToJail(Player $player): void {
        $this->jailedPlayers[$player->getName()] = [
            "x" => $player->getPosition()->getX(),
            "y" => $player->getPosition()->getY(),
            "z" => $player->getPosition()->getZ(),
            "level" => $player->getWorld()->getFolderName()
        ];
        if ($this->jailPosition instanceof Position) {
            $player->teleport($this->jailPosition);
            $player->sendMessage($this->jailMessage);
            $player->sendTitle($this->welcomeMessage, $this->welcomeSubtitle);
        }
    }

    private function releaseFromJail(Player $player): void {
        if (isset($this->jailedPlayers[$player->getName()])) {
            $positionData = $this->jailedPlayers[$player->getName()];
            $level = $this->getServer()->getWorldManager()->getWorldByName($positionData["level"]);
            if ($level !== null) {
                $player->teleport(new Position($positionData["x"], $positionData["y"], $positionData["z"], $level));
            }
            unset($this->jailedPlayers[$player->getName()]);
        } else {
            $player->teleport($this->getServer()->getWorldManager()->getDefaultWorld()->getSafeSpawn());
        }
        $player->sendMessage($this->unjailMessage);
    }
}
