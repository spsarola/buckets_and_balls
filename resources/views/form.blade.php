@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <h1>Welcome to Buckets and Balls</h1>
    <p>This is the home page that will suggests you best buckets combination</p>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Bucket Form</div>
                <div class="card-body">
                    <form action="{{ route('bucket.store') }}" method="POST" id="createBucketFrm">
                        @csrf
                        <div class="form-group">
                            <label for="bucket_name">Bucket Name:</label>
                            <input type="text" name="name" id="bucket_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="bucket_Volume">Volume (cubic inches):</label>
                            <input type="number" step=".01" name="volume" id="bucket_Volume" class="form-control"
                                required>
                        </div>
                        <button type="button" class="btn btn-primary" id="createBucketBtn">Create Bucket</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Ball Form</div>
                <div class="card-body">
                    <form action="{{ route('ball.store') }}" method="POST" id="createBallFrm">
                        @csrf
                        <div class="form-group">
                            <label for="ball_color">Ball Color:</label>
                            <input type="text" name="color" id="ball_color" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="ball_size">Size (cubic inches):</label>
                            <input type="number" step=".01" name="size" id="ball_size" class="form-control"
                                required>
                        </div>
                        <button type="button" class="btn btn-primary" id="createBallBtn">Create Ball</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <br>
    <br>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Bucket Suggestion Form</div>
                <div class="card-body">
                    <form action="{{ route('suggest-buckets') }}" id="suggestBucketsFrm" method="POST">

                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Result</div>
                <div class="card-body" id="resultBody">
                   
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function() {
            getTheBalls();
            suggestedBucketsList();
        });

        function capitalizeFirstLetter(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        }

        function getTheBalls() {

            // var data = new FormData($("#suggestBucketsFrm")[0]);
            $.ajax({
                url: "{{ route('ball.index') }}",
                type: "get",
                dataType: "json",
                async: true,
                processData: false,
                contentType: false,
                data: {},
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                },
                success: function(response) {

                    let frmHtml = ``;
                    if (response.data.length > 0) {
                        response.data.forEach(item => {
                            frmHtml += `
                            <div class="form-group">
                                <label for="${item.color}_balls">${capitalizeFirstLetter(item.color)} Balls: (size = ${item.size})</label>
                                <input type="number" name="balls[${item.color}]" id="${item.color}_balls" class="form-control" required>
                            </div>`;
                        });
                        frmHtml +=
                            `<button type="button" class="btn btn-primary" id="suggestBucketsBtn">Suggest Buckets</button>`;
                    }

                    $("#suggestBucketsFrm").html(frmHtml);
                },
                error: function(xhr, exception) {
                    var msg = "";
                    if (xhr.status === 0) {
                        msg = "Not connect.\n Verify Network." + xhr.responseText;
                    } else if (xhr.status == 404) {
                        msg = "Requested page not found. [404]" + xhr.responseText;
                    } else if (xhr.status == 500) {
                        msg = "Internal Server Error [500]." + xhr.responseText;
                    } else if (exception === "parsererror") {
                        msg = "Requested JSON parse failed.";
                    } else if (exception === "timeout") {
                        msg = "Time out error." + xhr.responseText;
                    } else if (exception === "abort") {
                        msg = "Ajax request aborted.";
                    } else {
                        msg = "Error:" + xhr.status + " " + xhr.responseText;
                    }

                }
            });
        }

        $(document).on('click', '#createBucketBtn', function() {
            var data = new FormData($("#createBucketFrm")[0]);
            $.ajax({
                url: "{{ route('bucket.store') }}",
                type: "POST",
                dataType: "json",
                async: true,
                processData: false,
                contentType: false,
                data: data,
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                },
                success: function(response) {
                    alert("Bucket added successfully!.")
                    $("#createBallFrm")[0].reset();
                },
                error: function(xhr, exception) {
                    var msg = "";
                    if (xhr.status === 0) {
                        msg = "Not connect.\n Verify Network." + xhr.responseText;
                    } else if (xhr.status == 404) {
                        msg = "Requested page not found. [404]" + xhr.responseText;
                    } else if (xhr.status == 500) {
                        msg = "Internal Server Error [500]." + xhr.responseText;
                    } else if (exception === "parsererror") {
                        msg = "Requested JSON parse failed.";
                    } else if (exception === "timeout") {
                        msg = "Time out error." + xhr.responseText;
                    } else if (exception === "abort") {
                        msg = "Ajax request aborted.";
                    } else {
                        msg = "Error:" + xhr.status + " " + xhr.responseText;
                    }

                }
            });
        });

        $(document).on('click', '#createBallBtn', function() {
            var data = new FormData($("#createBallFrm")[0]);
            $.ajax({
                url: "{{ route('ball.store') }}",
                type: "POST",
                dataType: "json",
                async: true,
                processData: false,
                contentType: false,
                data: data,
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                },
                success: function(response) {
                    alert("Ball added successfully!.")
                    $("#createBallFrm")[0].reset();
                    getTheBalls()
                },
                error: function(xhr, exception) {
                    var msg = "";
                    if (xhr.status === 0) {
                        msg = "Not connect.\n Verify Network." + xhr.responseText;
                    } else if (xhr.status == 404) {
                        msg = "Requested page not found. [404]" + xhr.responseText;
                    } else if (xhr.status == 500) {
                        msg = "Internal Server Error [500]." + xhr.responseText;
                    } else if (exception === "parsererror") {
                        msg = "Requested JSON parse failed.";
                    } else if (exception === "timeout") {
                        msg = "Time out error." + xhr.responseText;
                    } else if (exception === "abort") {
                        msg = "Ajax request aborted.";
                    } else {
                        msg = "Error:" + xhr.status + " " + xhr.responseText;
                    }

                }
            });
        });

        $(document).on('click', '#suggestBucketsBtn', function() {
            var data = new FormData($("#suggestBucketsFrm")[0]);
            $.ajax({
                url: "{{ route('suggest-buckets') }}",
                type: "POST",
                dataType: "json",
                async: true,
                processData: false,
                contentType: false,
                data: data,
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                },
                success: function(response) {
                    suggestedBucketsList();
                    $("#suggestBucketsFrm")[0].reset();
                },
                error: function(xhr, exception) {
                    var msg = "";
                    if (xhr.status === 0) {
                        msg = "Not connect.\n Verify Network." + xhr.responseText;
                    } else if (xhr.status == 404) {
                        msg = "Requested page not found. [404]" + xhr.responseText;
                    } else if (xhr.status == 500) {
                        msg = "Internal Server Error [500]." + xhr.responseText;
                    } else if (exception === "parsererror") {
                        msg = "Requested JSON parse failed.";
                    } else if (exception === "timeout") {
                        msg = "Time out error." + xhr.responseText;
                    } else if (exception === "abort") {
                        msg = "Ajax request aborted.";
                    } else {
                        msg = "Error:" + xhr.status + " " + xhr.responseText;
                    }

                }
            });
        });

        function suggestedBucketsList(){
            $.ajax({
                url: "{{ route('suggested-buckets-list') }}",
                type: "get",
                dataType: "json",
                async: true,
                processData: false,
                contentType: false,
                data: {},
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                },
                success: function(response) {
                    
                    let frmHtml = `<ol class="list-group list-group-numbered"> <p>Following are the suggested buckets:</p>`;
                    if (response.data.length > 0) {
                        response.data.forEach(item => {
                            frmHtml += `<li class="list-group-item">${item}</li>`;
                        });
                        frmHtml +=
                            `</ol>`;
                    }

                    $("#resultBody").html(frmHtml);
                },
                error: function(xhr, exception) {
                    var msg = "";
                    if (xhr.status === 0) {
                        msg = "Not connect.\n Verify Network." + xhr.responseText;
                    } else if (xhr.status == 404) {
                        msg = "Requested page not found. [404]" + xhr.responseText;
                    } else if (xhr.status == 500) {
                        msg = "Internal Server Error [500]." + xhr.responseText;
                    } else if (exception === "parsererror") {
                        msg = "Requested JSON parse failed.";
                    } else if (exception === "timeout") {
                        msg = "Time out error." + xhr.responseText;
                    } else if (exception === "abort") {
                        msg = "Ajax request aborted.";
                    } else {
                        msg = "Error:" + xhr.status + " " + xhr.responseText;
                    }

                }
            });
        }
    </script>

@endsection
