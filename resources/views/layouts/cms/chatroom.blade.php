@extends('layouts.cms.master')

@section('script')
    @include('layouts.cms.datatable_script')
    <script>
        $(function() {
            $('#data_table').DataTable({
                language: languageStrings,
                "lengthChange": false,
                "pageLength": 6,
                "bInfo" : false,
                "dom": '<"pull-left"f><"pull-right"l>tip',
                "order": [[ 2, "desc" ]] 
            });

        });

        $(function() {
            $('#data_table_2').DataTable({
                language: languageStrings,
                "lengthChange": false,
                "pageLength": 6,
                "bInfo" : false,
                "dom": '<"pull-left"f><"pull-right"l>tip'
            });

        });

        //____________________________________________
        //record 
        //reference:https://github.com/addpipe/simple-recorderjs-demo/blob/master/js/app.js
        //call 2 api
        //call ajax first: uploadVoiceMessage
        //if success then call post though modal: refreshAfterUpload

        URL = window.URL || window.webkitURL;

        var gumStream;                      //stream from getUserMedia()
        var rec;                            //Recorder.js object
        var input;                          //MediaStreamAudioSourceNode we'll be recording

        // shim for AudioContext when it's not avb. 
        var AudioContext = window.AudioContext || window.webkitAudioContext;
        var audioContext //audio context to help us record

        var recordButton = document.getElementById("recordButton");
        var stopButton = document.getElementById("stopButton");
        var pauseButton = document.getElementById("pauseButton");

        //add events to those 2 buttons
        recordButton.addEventListener("click", startRecording);
        stopButton.addEventListener("click", stopRecording);
        pauseButton.addEventListener("click", pauseRecording);

        function startRecording() {
            console.log("recordButton clicked");

            /*
                Simple constraints object, for more advanced audio features see
                https://addpipe.com/blog/audio-constraints-getusermedia/
            */
            
            var constraints = { audio: true, video:false }

            /*
                Disable the record button until we get a success or fail from getUserMedia() 
            */

            recordButton.disabled = true;
            recordButton.value="{{ __('cms_chatroom.recording') }}";
            stopButton.disabled = false;
            pauseButton.disabled = false

            /*
                We're using the standard promise based getUserMedia() 
                https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia
            */

            navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
                console.log("getUserMedia() success, stream created, initializing Recorder.js ...");

                /*
                    create an audio context after getUserMedia is called
                    sampleRate might change after getUserMedia is called, like it does on macOS when recording through AirPods
                    the sampleRate defaults to the one set in your OS for your playback device
                */
                audioContext = new AudioContext();

                //update the format 
                document.getElementById("formats").innerHTML="Format: 1 channel pcm @ "+audioContext.sampleRate/1000+"kHz"

                /*  assign to gumStream for later use  */
                gumStream = stream;
                
                /* use the stream */
                input = audioContext.createMediaStreamSource(stream);

                /* 
                    Create the Recorder object and configure to record mono sound (1 channel)
                    Recording 2 channels  will double the file size
                */
                rec = new Recorder(input,{numChannels:1})

                //start the recording process
                rec.record()

                console.log("Recording started");

            }).catch(function(err) {
                //enable the record button if getUserMedia() fails
                recordButton.disabled = false;
                stopButton.disabled = true;
                pauseButton.disabled = true
            });
        }

        function pauseRecording(){
            console.log("pauseButton clicked rec.recording=",rec.recording );
            if (rec.recording){
                //pause
                rec.stop();
                pauseButton.innerHTML="{{ __('cms_chatroom.resume') }}";
            }else{
                //resume
                rec.record()
                pauseButton.innerHTML="{{ __('cms_chatroom.pause') }}";

            }
        }

        function stopRecording() {
            console.log("stopButton clicked");
            $("#recordModal").modal("show");

            //disable the stop button, enable the record too allow for new recordings
            stopButton.disabled = true;
            recordButton.disabled = false;
            pauseButton.disabled = true;

            //reset button just in case the recording is stopped while paused
            pauseButton.innerHTML="{{ __('cms_chatroom.pause') }}";
            
            //tell the recorder to stop the recording
            rec.stop();

            //stop microphone access
            gumStream.getAudioTracks()[0].stop();

            //create the wav blob and pass it on to createDownloadLink
            rec.exportWAV(createPlayerOnModal);
 
        }

        function createPlayerOnModal(blob) {

            var url = URL.createObjectURL(blob);
            var au = document.createElement('audio');
            var li = document.createElement('div');

            

            //add controls to the <audio> element
            au.controls = true;
            au.src = url;

            //add the new audio element to li
            li.appendChild(au);
            
            recordingsList.appendChild(li);

        }

        $('#recordModal').on('show.bs.modal', function (event) { 
                // var button = $(event.relatedTarget);
                // var current_directory = button.data('current');
                var modal = $(this);
                var $form = modal.find('form');

                var title = '{{ __('cms_chatroom.chatroom_title') }}' ;
                modal.find('.modal-title').text(title);

                //$('input[name=_method]').remove();
                $form.attr('action', '/cms/chatroom/refreshAfterUpload');
        });

        $('#recordModal').on('submit', function (event) { 
                event.preventDefault();
                $("#recordModal").modal("hide");
                rec.exportWAV(send);
        });

        $('#recordModal').on('hidden.bs.modal', function (event) { 
                location.reload();

        });



        function send(blob) {

            var fd = new FormData();
            var filename = new Date().toISOString().slice(0, 10)+" "+new Date().toISOString().slice(11, 19);
            filename= filename+".wav";
            fd.append('name', filename);
            fd.append('chat_id', {!! $chat_id !!});
            fd.append('voice_message', blob);

            $.ajax({
                'url': '/cms/chatroom/uploadVoiceMessage',
                'method': "POST",
                'headers': {'X-CSRF-TOKEN': "{{ csrf_token() }}"},
                'data': fd,
                'processData': false,
                'contentType': false
            })
            .done(function(response){
                form = document.getElementById("form"); //assuming only form.
                form.submit();
            })
            .fail(function(message){

            });
        }
        //------------------------
        //scroll to bottom
        var objDiv = document.getElementById("body_message_container");
        objDiv.scrollTop = objDiv.scrollHeight;

        //Add new chat button

        $('.add_new_chat_container').hide();
        $('#addNewChatButton').on('click',
          function() {
            $('.recent_chat_container,.add_new_chat_container').toggle(100);
            if ($(this).val() == "{{ __('cms_chatroom.addchat') }}") { 
                $(this).val("{{ __('cms_chatroom.chats') }}"); 
            } else { 
                $(this).val("{{ __('cms_chatroom.addchat') }}"); 
            }; 
          }
        );




    </script>

@endsection

@section('style')
    <style>

        .chat_container{
            width: 40%; 
            float:left;
        }

        .recent_chat_container{
            height: 600px;
        }

        .add_new_chat_container{
            height: 600px;
        }

        .message_container{
            width: 60%; 
            float:left;
            height:590px;
            overflow: hidden;
        }

        .header_message_container{
            overflow: hidden;
        }

        .body_message_container{
            height: 550px; 
            overflow-y: scroll;
        }

        .container {
        }

        .left {
            border: 2px solid #dedede;
            background-color: #f1f1f1;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
            float:left;
            width:350px;
        }

        .right {
            border: 2px solid #dedede;
            background-color: #f1f1f1;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
            float:right;
            width:350px;
        }

        .container::after {
            content: "";
            clear: both;
            display: table;
        }

        .container img {
            float: left;
            max-width: 60px;
            width: 100%;
            margin-right: 20px;
            border-radius: 50%;
        }

        .container img.right {
            float: right;
            margin-left: 20px;
            margin-right:0;
        }

        .time-right {
            float: right;
            color: #aaa;
        }

        #controls{
            margin-top: 10px;
            float:right;
        }

        #recordButton{
            border-radius: 5px;
        }

        #addNewChatButton{
            float: right;
        }



    </style>
@endsection

@section('content')
    <h3>{{ __('cms_master.menu_chatroom') }}</h3>
    @include('layouts.cms.success')
    @include('layouts.cms.error_list', ['errors' => $errors])

    <a href="/cms/chatroom/1/0">{{ __('cms_language.chinese') }}</a>
    <a href="/cms/chatroom/2/0">{{ __('cms_language.english') }}</a>
    <a href="/cms/chatroom/3/0">{{ __('cms_language.japanese') }}</a>
    <a href="/cms/chatroom/4/0">{{ __('cms_language.nepali') }}</a><br><br>

    <div class="chat_container">

        <div class="recent_chat_container">

            <h4>{{ __('cms_chatroom.chats') }}</h4>

           <div class="table_container table-responsive-lg">
                <table id="data_table" class="table table-hover table-md">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('cms_chatroom.id') }}</th>
                            <th>{{ __('cms_chatroom.name') }}</th>
                            <th>{{ __('cms_chatroom.date') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                         @foreach ($chats as $chat)
                            <tr>
                                <td><a href=/cms/chatroom/{{ $chat->local_id }}/{{ $chat->chat_id}}>{{$chat->uuid}}</a></td>
                                <td>{{$chat->name}}</td>
                                <td>{{$chat->date}}</td>
                            </tr>
                        @endforeach  
                    </tbody>
                </table>
            </div>

        </div>

        <div class="add_new_chat_container">

            <h4>{{ __('cms_chatroom.addchat') }}</h4>

            <div class="table_container table-responsive-lg">
                <table id="data_table_2" class="table table-hover table-md">
                    <thead class="thead-light">
                    <tr>
                        <th>{{ __('cms_chatroom.id') }}</th>
                        <th>{{ __('cms_chatroom.name') }}</th>
                        <th>{{ __('cms_chatroom.action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($blesscup as $cup)
                            <tr>
                                <td>{{ $cup->uuid }}</td>
                                <td>{{ $cup->name }}</td>
                                <td><a href=/cms/chatroom/createChat/{{ $cup->uuid}}><button class="btn btn-default" id="add"><i class="fa fa-plus"></i></button></a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

        <input class="btn btn-success" type="button" value="{{ __('cms_chatroom.addchat') }}" id="addNewChatButton"></input> 

    </div>


    <div class="message_container">
        <div class="header_message_container" >
            <h4>{{ __('cms_chatroom.blesscupid') }}{{$friend->uuid." "}}{{ __('cms_chatroom.username') }}{{$friend->name}}</h4>
        </div>


        <div class="body_message_container" id="body_message_container">
            @foreach ($messages as $message)
                @if( $message->user_id == $friend->user_id )         
                    <div class="container">
                        <div class="left">
                          <span class="time-right">{{$message->created_at}}</span>                    
                          <audio controls id="" src="{{asset('/storage/wav/')}}/{{$message->local_filename}}" type="audio/wav"></audio>
                          <a href=/cms/chatroom/deleteVoiceMessage/{{ $message->id}}><i class="fa fa-trash fa-lg"></i></a>
                        </div>
                    </div>  
                @else
                    <div class="container">
                        <div class="right">
                          <span class="time-right">{{$message->created_at}}</span>
                          <audio height="10" controls id="" src="{{asset('/storage/wav/')}}/{{$message->local_filename}}" type="audio/wav"></audio>
                          <a href=/cms/chatroom/deleteVoiceMessage/{{ $message->id}}><i class="fa fa-trash fa-lg"></i></a>
                        </div>
                    </div>          
                @endif
            @endforeach
        </div>

    </div>
    <div id="controls">
        <input class="btn btn-danger" type="button" value="{{ __('cms_chatroom.record') }}" id="recordButton"></input> 
         <button class="btn btn-default" id="pauseButton" disabled>{{ __('cms_chatroom.pause') }}</button>
         <button class="btn btn-default" id="stopButton" disabled data-toggle="modal" data-target="#recordModal">{{ __('cms_chatroom.stop') }}</button>
    </div>
    <div style="display: none;" id="formats"></div>

    <div class="modal fade" id="recordModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id=form method="POST">
                    {{ csrf_field() }}
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ol id="recordingsList"></ol>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('cms_master.back') }}</button>
                        <button class="btn btn-success" id=send>{{ __('cms_chatroom.send') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.rawgit.com/mattdiamond/Recorderjs/08e7abd9/dist/recorder.js"></script>
@endsection




