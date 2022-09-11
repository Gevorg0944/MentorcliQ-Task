<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>MentorcliQ Task</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <link rel="stylesheet" href="{{asset('main.css')}}" crossorigin="anonymous">

        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
        </style>
    </head>
    <body class="antialiased">

        <div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white border-bottom box-shadow">
            <h5 class="my-0 mr-md-auto font-weight-normal">MentorcliQ</h5>
        </div>

        <div class="content">
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{route('upload.employees')}}" method="post" enctype="multipart/form-data">
                           @csrf

                            <div class="form-group">
                                <label for="file">File</label>
                                <input type="file" accept="text/csv" name="file" class="form-control" id="file">
                            </div>

                            <button class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>

                @if(session()->has('scoreData'))
                    @php
                        $scoreData = session()->get('scoreData');
                    @endphp
                    <hr />
                    <div class="row">
                        <div class="col-md-6">
                            <p>{{$scoreData['averageText']}}</p>
                            <ul>
                            @foreach($scoreData['result'] as $score)
                                <li>{{$score['text']}}</li>
                            @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </body>
</html>
