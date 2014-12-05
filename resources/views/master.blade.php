<!doctype html>
<html>
  <head>
    <title>Lycee TCG Card Database</title>
    <meta name="description" content="Unofficial website for the Lycee Trading Card Game and its fan-translations">
    <meta name="author" content="Attila Szeremi (amcsi)">
    <meta name="keywords" content="lycee, tcg, trading card game, english, japanese, translation, database">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/reset.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('v/jquery-ui/css/humanity/jquery-ui-1.10.3.custom.css') }}">
  <meta charset="utf-8">
    <script type='text/javascript' src='{{ asset('js/jquery-1.10.1.min.js') }}'></script>
    <script type='text/javascript' src='{{ asset('v/jquery-ui/js/jquery-ui-1.10.3.custom.min.js }}'></script>
    <script type='text/javascript' src='{{ asset('js.js') }}'></script>

</head>
  <body>
    @if (!empty($shared['google_analytics']['code']))
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', {!! json_encode($shared['google_analytics']['code']) !!}, {!! json_encode($shared['google_analytics']['domain']) !!});
        ga('send', 'pageview');
    </script>
    @endif

    <div id='header'>

    </div>
    <div id='nav'>
        <ul class="nav left">
            <li><a href='{{ URL::route('search') }}'>Card search</a></li>
            <li><a href=forums_url_base'>Forums</a></li>
        </ul>
        <ul class="nav right">
            @if ($forumsLoginBase = 'forums_url_base_https' /* 'forums_url_base' */) @endif
            @if ($user['isLoggedIn'])
            <li><div><span>Welcome, <span class='nick'>{{ $user['usernameClean'] }}</span>!</span></div></li>
            <li><a href='{{ $forumsLoginBase }}/ucp.php?mode=logout&amp;sid={{ $user['sessionId'] }}&amp;redirect={{ URL::current() }}'>Logout</a></li>
            @else
            <li><div><span>Welcome, Guest!</span></div></li>
            <li><a href='{{ $forumsLoginBase }}/ucp.php?mode=login&amp;redirect={{ URL::current() }}'>Login</a></li>
            @endif
        </ul>
    </div>

    <div id='content'>
        @section('content')
    </div>
    <div id='footer'>
        <div class="optimized">This website is optimized for the following browsers: <span class="browser">Opera 10+</span>, <span class="browser">FireFox 3.5+</span>, <span class="browser">Internet Explorer 7+</span>; and the resolution <span class="resolution">800x600</span>
        </div>
        <div>
            <span class="coding"><span class="property">Coding:</span> <a href="mailto:amcsi@mailbox.hu">amcsi</a></span><br>
            <span class="design"><span class="property">Design:</span> <a href="mailto:amcsi@mailbox.hu">amcsi</a></span>
        </div>

        {{--
        <?php if (isset($this->amysqlQueriesData)): ?>
            <div>MySQL query information:</div>
            <?php if ($this->amysqlQueriesData): ?>
            <table class="queryInfoTable">
                <tr>
                    <th class="no">No.</th>
                    <th class="query">Query</th>
                    <th class="time">Time</th>
                </tr>
                <tr>
                    <td>Total</td>
                    <td>&nbsp;</td>
                    <td><?= $this->amysqlTotalTime ?></td>
                </tr>
                <?php foreach ($this->amysqlQueriesData as $index => $row): ?>
                <tr>
                    <td>#<?= $index ?></td>
                    <td><?= $this->escapeHtml($row['query']) ?></td>
                    <td><?= $this->escapeHtml($row['time']) ?></td>
                </tr>
                <?php endforeach ?>
            </table>
            <?php else: ?>
            <div>No MySQL queries were run</div>
            <?php endif ?>

        <?php endif ?>
        --}}
    </div>







  </body>
</html>
