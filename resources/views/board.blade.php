@extends('layouts.app')

@section('scripts')
<script language="JavaScript">

    function getMensaje(){
    var aFrases=new Array();
    aFrases[0]="S : Single responsibility  🧐 Recuerda que cada clase debe tener una sola responsabilidad, asi evitaremos que las clases realicen tareas que no deberian realizar. 🌟 ";
    aFrases[1]="O : Open/Closed  👉 Abierto para extension pero cerrado para modificacion, aun que parezca facil, lo complicado es precedir por donde se debe extender y que no tengamos que refactorizar v ";
    aFrases[2]="L : Liskov substitution 👴 --> 👦  La subclase puede ser sustituida por su superclase ✨";
    aFrases[3]="I : Interface segregation 👆 Reaprovecha las intefaces en otras clases!! 👊 ";
    aFrases[4]="D : Dependency inversion  🤟 Los módulos de alto nivel no deberían depender de módulos de bajo nivel. Ambos deberían depender de abstracciones.";
    return(aFrases[Math.floor(Math.random() * aFrases.length)]);
    }
    function checkResult()
    {
        var win = false;
        // Top Row
        if(
            $('#block-1.player-{{$playerType}}:checked').length &&
            $('#block-2.player-{{$playerType}}:checked').length &&
            $('#block-3.player-{{$playerType}}:checked').length
        )
        {
            win = true;
        }
        // Middle Row
        else if(
            $('#block-4.player-{{$playerType}}:checked').length &&
            $('#block-5.player-{{$playerType}}:checked').length &&
            $('#block-6.player-{{$playerType}}:checked').length
        )
        {
            win = true;
        }
        // Bottom Row
        else if(
            $('#block-7.player-{{$playerType}}:checked').length &&
            $('#block-8.player-{{$playerType}}:checked').length &&
            $('#block-9.player-{{$playerType}}:checked').length
        )
        {
            win = true;
        }
        // Left Col
        else if(
            $('#block-1.player-{{$playerType}}:checked').length &&
            $('#block-4.player-{{$playerType}}:checked').length &&
            $('#block-7.player-{{$playerType}}:checked').length
        )
        {
            win = true;
        }
        // Center Col
        else if(
            $('#block-2.player-{{$playerType}}:checked').length &&
            $('#block-5.player-{{$playerType}}:checked').length &&
            $('#block-8.player-{{$playerType}}:checked').length
        )
        {
            win = true;
        }
        // Right Col
        else if(
            $('#block-3.player-{{$playerType}}:checked').length &&
            $('#block-6.player-{{$playerType}}:checked').length &&
            $('#block-9.player-{{$playerType}}:checked').length
        )
        {
            win = true;
        }
        // Diagonal Left to Right
        else if(
                $('#block-1.player-{{$playerType}}:checked').length &&
                $('#block-5.player-{{$playerType}}:checked').length &&
                $('#block-9.player-{{$playerType}}:checked').length
        )
        {
            win = true;
        }
        // Diagonal Right to Left
        else if(
                $('#block-3.player-{{$playerType}}:checked').length &&
                $('#block-5.player-{{$playerType}}:checked').length &&
                $('#block-7.player-{{$playerType}}:checked').length
        )
        {
            win = true;
        }

        if(!win){
            if($('input[type=radio]:checked').length == 9){
                return 'tie';
            }
        }
        else{
            return 'win';
        }

        return false;
    }


    var pusher = new Pusher('83b70f7000ad7616c8af', {cluster:'mt1',forceTLS:true });
    var gamePlayChannel = pusher.subscribe('game-channel-{{$id}}-{{$otherPlayerId}}');
    var gameOverChannel = pusher.subscribe('game-over-channel-{{$id}}-{{$otherPlayerId}}');

    gamePlayChannel.bind('App\\Events\\Play', function(data){
        $('#block-' + data.location).removeClass('player-{{$playerType}}').addClass('player-' + data.type);
        $('#block-' + data.location).attr('checked', true);
        $('input[type=radio]').removeAttr('disabled');
        $('.profile-username').html('You are next!');
    });


    gameOverChannel.bind('App\\Events\\GameOver', function(data){
        $('#block-' + data.location).removeClass('player-{{$playerType}}').addClass('player-' + data.type);
        $('#block-' + data.location).attr('checked', true);

        if(data.type=='x'){
        if(data.result == 'win'){
            $('.profile-username').html('4+1 Lose');
        }
        else{
            $('.profile-username').html('Its a tie!');
        }
        $('#exit-button').show();


        }

        if(data.type=='o'){
        if(data.result == 'win'){
            $('.profile-username').html('SOLID LOSE!');
        }
        else{
            $('.profile-username').html('Its a tie!');
        }
        $('#exit-button').show();

        }
     

        
    });
    $(document).ready(function(){
        $('input[type=radio]').on('click', function(){
            $('input[type=radio]').attr('disabled', true);
            var result = checkResult();
          
            if(!result){
                $('.profile-username').html('Waiting on player 2...');
                $.ajax({
                    url: '/play/{{$nextTurn->game_id}}',
                    method: 'POST',
                    data: {
                        location: $(this).val(),
                        _token: $('input[name=_token]').val()
                    },
                    success: function(data){
                        //
                    }
                });
            }
            else{
                if(result == 'win'){

                    $('.profile-username').html( getMensaje() );
                    
                }
                else{
                    $('.profile-username').html('Its a tie!');
                }
                $('#exit-button').show();
                $.ajax({
                    url: '/game-over/{{$nextTurn->game_id}}',
                    method: 'POST',
                    data: {
                        location: $(this).val(),
                        result: result,
                        _token: $('input[name=_token]').val()
                    },
                    success: function(data){
                        //
                    }
                });
                
            }
        });
    });
</script>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="profile-info">
                <div class="profile-username">
                    {{ $user->id == $nextTurn->player_id ? "Your are next!" : "Waiting on player 2..." }}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="tic-tac-toe">
                @foreach($locations as $index => $location)
                <input type="radio"
                    class="player-{{ $location["checked"] ? $location["type"] : $playerType }} {{ $location["class"] }}"
                    id="block-{{$index}}"
                    value="{{$index}}"
                    {{ $location["checked"] ? "checked" : "" }}
                    {{ $user->id != $nextTurn->player_id ? "disabled" : "" }}/>
                <label for="block-{{$index}}"></label>
                @endforeach
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 text-center">
            <a id="exit-button" href="/home" class="btn btn-lg btn-primary" style="display: none;">Exit Game</a>
        </div>
    </div>
</div>
{{ csrf_field() }}
@endsection
