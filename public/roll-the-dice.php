<?php

$players = rollDice();
// $players = [
//     [6,6,6,6,6,6],
//     [6,6,6,6,6,6],
//     [6,6,6,6,6,6],
//     [6,6,6,6,6,6]
// ];
$winner = null;
$round = 1;

while(is_null($winner)){
    echo "\nRound $round\n";
    echo "After the dice rolled\n";
    for($i = 0; $i < count($players); $i++){
        echo "Player-".($i+1).": ";
        for($j = 0; $j < count($players[$i]); $j++){
            echo $players[$i][$j]." ";
        }
        echo "\n";
    }
    $players = letsPlayByTheRules($players);
    echo "\nAfter the dice moved or/and removed\n";
    for($i = 0; $i < count($players); $i++){
        echo "Player-".($i+1).": ";
        for($j = 0; $j < count($players[$i]); $j++){
            echo $players[$i][$j]." ";
        }
        echo "\n";
    }
    $_winners = array_filter($players, function($player){
        return (count($player)==0);
    });
    if(count($_winners)==1){
        $_winner_index = array_keys($_winners);
        echo "\nWinner is Player-".($_winner_index[0]+1)."\n\n";
        $winner = $_winners;
    } else if(count($_winners)>1){
        $_winner_index = array_keys($_winners);
        echo "\nIt's a tie for ";
        for($i = 0; $i < count($_winner_index); $i++){
            
            echo "Player-". ($_winner_index[$i]+1);
            if($i == count($_winner_index)-2){
                echo " and ";
            } else if($i < count($_winner_index)-1){
                echo ", ";
            }
        }
        echo "\n\n";
        $winner = $_winners;
    }

    $diceCount = getDiceCount($players);
    $players = rollDice($diceCount);

    $round++;
}


function rollDice($initialDiceCount = [6,6,6,6]){
    $players = [];
    for($i = 0; $i < count($initialDiceCount); $i++){
        $players[] = [];
        for($j = 0; $j < $initialDiceCount[$i]; $j++){
            $players[$i][] = rand(1,6);
        }
    }
    return $players;
}

function getDiceCount($players){
    $count = [];
    for($i = 0; $i < count($players); $i++){
        $count[] = count($players[$i]);
    }
    return $count;
}

// move the dice by the rules
function letsPlayByTheRules($players){
    $_players = $players;
    for($i=0;$i<count($_players); $i++){
        $_players[$i] = array_values(
            array_filter($_players[$i], function($_dice){
                return $_dice!=6 && $_dice != 1;
            })
        );
    }
    for($i = 0; $i < count($players); $i++){
        for($j = 0; $j < count($players[$i]); $j++){
            if($players[$i][$j]==1){
                $_players[($i+1)%count($players)][] = 1;
            }
        }
    }
    return $_players;
}