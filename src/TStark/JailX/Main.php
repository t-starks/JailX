<?php

namespace TStark\JailX;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\scheduler\ClosureTask;

class Main extends PluginBase implements Listener {

    private $jailPosition;
    private $jailMessage;
    private $unjailMessage;
    private $welcomeMessage;
    private $welcomeSubtitle;
    private $jailedPlayers = [];
    private $blockedCommands;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->jailPosition = $this->getJailPositionFromConfig();
        $this->jailMessage = $this->getConfig()->get("jail-message", "§cYou have been sent to jail!");
        $this->unjailMessage = $this->getConfig()->get("unjail-message", "§aYou have been released from jail!");
        $this->welcomeMessage = $this->getConfig()->get("welcome-message", "§eWelcome to jail");
        $this->welcomeSubtitle = $this->getConfig()->get("welcome-subtitle", "§6Good luck during your stay");
<<<<<<< HEAD
        $this->blockedCommands = $this->getConfig()->get("blocked-commands", ["hub", "lobby", "tp", "tpa", "spawn"]);
=======
>>>>>>> ae39fb60dc4cc788647dcd7f972ac58c7b0f241d
        $this->loadJailedPlayers();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
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
                    if (count($args) >= 1) {
                        $target = $this->getServer()->getPlayerByPrefix($args[0]);
                        if ($target instanceof Player) {
<<<<<<< HEAD
                            $duration = count($args) >= 2 ? intval($args[1]) : 0;
                            $this->sendToJail($target, $duration);
=======
                            $this->sendToJail($target);
>>>>>>> ae39fb60dc4cc788647dcd7f972ac58c7b0f241d
                            $sender->sendMessage(TextFormat::GREEN . "Player {$target->getName()} was sent to jail.");
                        } else {
                            $sender->sendMessage(TextFormat::RED . "Player {$args[0]} is not online.");
                        }
                    } else {
<<<<<<< HEAD
                        $sender->sendMessage(TextFormat::RED . "Usage: /jail <nick> [duration]");
=======
                        $sender->sendMessage(TextFormat::RED . "Usage: /jail <nick>");
>>>>>>> ae39fb60dc4cc788647dcd7f972ac58c7b0f241d
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
<<<<<<< HEAD
                }
                return true;
            case "jailstatus":
                if ($sender->hasPermission("jailx.command.jailstatus")) {
                    if (count($args) === 1) {
                        $playerName = $args[0];
                        if (isset($this->jailedPlayers[$playerName])) {
                            $jailedData = $this->jailedPlayers[$playerName];
                            $timeLeft = isset($jailedData['releaseTime']) ? ($jailedData['releaseTime'] - time()) : "Indefinite";
                            $sender->sendMessage(TextFormat::GREEN . "Player {$playerName} is in jail. Time left: {$timeLeft}");
                        } else {
                            $sender->sendMessage(TextFormat::RED . "Player {$playerName} is not in jail.");
                        }
                    } else {
                        $sender->sendMessage(TextFormat::RED . "Usage: /jailstatus <nick>");
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "You do not have permission to use this command.");
                }
                return true;
            case "jaillist":
                if ($sender->hasPermission("jailx.command.jaillist")) {
                    $jailedList = "Jailed Players:\n";
                    foreach ($this->jailedPlayers as $playerName => $data) {
                        $timeLeft = isset($data['releaseTime']) ? ($data['releaseTime'] - time()) : "Indefinite";
                        $jailedList .= "{$playerName}: Time left: {$timeLeft}\n";
                    }
                    $sender->sendMessage(TextFormat::GREEN . $jailedList);
                } else {
                    $sender->sendMessage(TextFormat::RED . "You do not have permission to use this command.");
=======
>>>>>>> ae39fb60dc4cc788647dcd7f972ac58c7b0f241d
                }
                return true;
            default:
                return false;
        }
    }

    public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event): void {
        $player = $event->getPlayer();
        $command = strtolower(explode(" ", $event->getMessage())[0]);
        if (isset($this->jailedPlayers[$player->getName()]) && in_array($command, $this->blockedCommands)) {
            $player->sendMessage(TextFormat::RED . "You cannot use this command while in jail.");
            $event->cancel();
        }
    }

    private function sendToJail(Player $player, int $duration): void {
        $this->jailedPlayers[$player->getName()] = [
            "x" => $player->getPosition()->getX(),
            "y" => $player->getPosition()->getY(),
            "z" => $player->getPosition()->getZ(),
            "level" => $player->getWorld()->getFolderName(),
            "releaseTime" => $duration > 0 ? time() + $duration : null
        ];
        if ($this->jailPosition instanceof Position) {
            $player->teleport($this->jailPosition);
            $player->sendMessage($this->jailMessage);
            $player->sendTitle($this->welcomeMessage, $this->welcomeSubtitle);
        }
        if ($duration > 0) {
            $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
                $this->releaseFromJail($player);
            }), $duration * 20);
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
