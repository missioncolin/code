    <div class="clearfix"></div>
    </section>
    
    <footer>
        <div>
            <h3>Intervue <span>Bringing People Into Focus</span></h3>
            <div id="footerNav">
                <a href="#">About Us</a> &bull; <a href="#">Privacy Policy</a>
            </div>
        <div class="clearfix"></div>
        </div>
    </footer>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="/themes/PanicBob/js/vendor/jquery-1.7.2.min.js"><\/script>')</script>

    <script src="/themes/Intervue/js/plugins.js"></script>
    <script src="/themes/Intervue/js/main.js"></script>
    <script src="/js/placeholderShiv.js"></script>
    <script src="/js/iOS-Orientation-Fix.js"></script>
    <?php
    if(isset($quipp->js['footer']) && is_array($quipp->js['footer'])) {
        foreach($quipp->js['footer'] as $val) {
            if ($val != '') { echo "<script src=\"{$val}\"></script>\n    "; }
        }
    }
    ?>
    <script src="/themes/<?php echo basename(__DIR__); ?>/site.js"></script>
    <?php
    if(isset($quipp->js['onload']) && !empty($quipp->js['onload'])) {
        echo "<script>$(function() { {$quipp->js['onload']} }); </script>\n";
    }
    ?>
    <script>
        var _gaq=[['_setAccount','<?php echo Quipp()->config('google.ga_tracking_id'); ?>'],['_trackPageview']];
        (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
        g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
        s.parentNode.insertBefore(g,s)}(document,'script'));
    </script>
</body>
</html>
