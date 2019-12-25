$(document).ready(function(){
    let round = 1;
    let gameStarted = false;
    let rolledDice = null;
    let winners = null;
    $(document).on('click', '#roll-button',  function(e){
        e.preventDefault();
        // $("#loadModal").modal({
        //     backdrop: "static", 
        //     keyboard: false, 
        //     show: true
        // });
        if(!gameStarted){
            $('#winner-label').html('').hide();
            $('.round-container').html('').hide();
        }
        $('#round-label').html("Round - "+round);
        console.log("%cRound-"+round, "color: red")
        console.log("%cAfter Dice Role: ", "color: black")
        $('#roll-button').attr("disabled", true);
        let roundHtml = "<p>Round-"+(round)+" : ";
        $.ajax({
            url: '/roll-dice',
            type: 'POST',
            data: gameStarted?JSON.stringify({players: rolledDice}):null,
            contentType: 'application/json',
            dataType: 'json',
            processData: false,
            cache: false,
            success: function (data) {
                let to = 0;
                rolledDice = data;
                $('.dice-container').map(function(idx, el){
                    console.log("%cPlayer-"+(idx+1), "color: green")
                    console.log(rolledDice[idx]);
                    let roundContainer = roundHtml + JSON.stringify(rolledDice[idx])+"</p>";
                    (function(idx, el, data){
                        if(data && data.length){
                            setTimeout(function(){
                                rollADie({ element: el, numberOfDice: data.length, values: data, delay: 5000, noSound: false, callback: function(){
                                }});
                            }, to);
                            $(el).parent().children('.round-container').append(roundContainer);
                        }
                    })(idx, el, rolledDice[idx]);
                    if(idx==$('.dice-container').length-1){
                        setTimeout(function(){
                            $('#roll-button').removeAttr("disabled");
                        }, to + 7500)
                    }
                    to += 5000;
                });
                console.log("%cAfter Dice Moved or/and Removed: ", "color: black")
                $.ajax({
                    url: '/move-dice',
                    type: 'POST',
                    data: JSON.stringify({players: rolledDice}),
                    contentType: 'application/json',
                    dataType: 'json',
                    cache: false,
                    success: function (data) {
                        rolledDice = data.players;
                        $('.dice-container').map(function(idx, el){
                            console.log("%cPlayer-"+(idx+1), "color: green")
                            console.log(rolledDice[idx]);
                            let roundContainer = " -> " + JSON.stringify(rolledDice[idx]);
                            setTimeout(function(){
                                $(el).parent().children('.round-container').children('p').last().append(roundContainer);
                                $(el).parent().children('.round-container').show();
                            }, to);
                        });
                        if(data.winners && data.winners.length){
                            winners = data.winners;
                            gameStarted = false;
                            round = 1;
                            let winnerLabel = '';
                            if(winners.length==1){
                                winnerLabel = 'The WINNER is Player-'+(winners[0]+1);
                            } else{
                                let winnerPlayer = "";
                                for(let i = 0; i < winners.length; i++){
                                    winnerPlayer += "Player-"+(winners[i]+1);
                                    if(i == winners.length-2){
                                        winnerPlayer += " and ";
                                    } else if(i < winners.length-1){
                                        winnerPlayer += ", ";
                                    }
                                }
                                winnerLabel = "it\'s a TIE for "+(winnerPlayer);
                            }
                            console.log(to + 5000);
                            setTimeout(function(){
                                $('#winner-label').html(winnerLabel).show();
                                alert(winnerLabel);
                            }, to);
                        }
                        $("#loadModal").modal({show: false});
                    },
                }).done(function(){
                });
                $("#loadModal").modal({show: false});
            },
        }).done(function(){
            $("#loadModal").modal({show: false});
        });
        gameStarted = true;
        round++;
    });
});