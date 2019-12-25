<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
ini_set('memory_limit', '-1');
function script_tag($src, $asset = false) {
    return '<script src="' . $src . '" ></script>';
}

$renderer = new PhpRenderer('../public/');

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

$app->get('/', function (Request $request, Response $response, $args) use ($app, $renderer) {
    return $renderer->render($response, 'index.phtml');
});

$app->get('/simple-demo', function (Request $request, Response $response, $args) use ($app, $renderer) {
    return $renderer->render($response, 'simple_simulation.phtml');
});

$app->post('/roll-dice', function (Request $request, Response $response, $args){
    $body = json_decode($request->getBody(), true);
    $players = isset($body['players'])?rollDice(getDiceCount($body['players'])):rollDice();
    $response->getBody()->write(json_encode($players));
    return $response;
});

$app->post('/move-dice', function (Request $request, Response $response, $args){
    $body = json_decode($request->getBody(), true);
    $players = letsPlayByTheRules($body['players']);
    $winners = null;
    $_winners = array_filter($players, function($player){
        return (count($player)==0);
    });
    if(count($_winners)>=1){
        $_winner_index = array_keys($_winners);
        $winners = $_winner_index;
    } 
    $response->getBody()->write(json_encode(['players'=>$players, 'winners'=>$winners]));
    return $response;
});

$app->run();