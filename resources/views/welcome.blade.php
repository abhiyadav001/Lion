<!DOCTYPE html>
<html>
    <head>
        <title>Laravel</title>

        <link href="//fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                color: #B0BEC5;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 96px;
                margin-bottom: 40px;
            }

            .quote {
                font-size: 24px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">Laravel 5</div>
                <div class="quote">{{ Inspiring::quote() }}</div>

                <script src="http://autobahn.s3.amazonaws.com/js/autobahn.min.js"></script>
                <script>
                    var conn = new ab.Session('ws://127.0.0.1:8080',
                        function() {
                            conn.subscribe('kittensCategory', function(topic, data) {
                                // This is where you would add the new article to the DOM (beyond the scope of this tutorial)
                                console.log('New article published to category "' + topic + '" : ' + data.title);
                            });
                        },
                        function() {
                            console.warn('WebSocket connection closed');
                        },
                        {'skipSubprotocolCheck': true}
                    );
                </script>
            </div>
        </div>
    </body>
</html>
