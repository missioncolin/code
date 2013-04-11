    <div class="clearfix"></div>
    </section>
    
    <?php if (strpos($user->username, 'newuser') === false) { 

        if (strpos($meta['body_id'], 'apply') !== false) { }

        else { ?>
    <footer>
        <div>
            <h3>Intervue <span>Bringing People Into Focus</span></h3>
            <div id="footerNav">
                <a href="/about-us">About Us</a> &bull; <a href="/privacy-policy">Privacy Policy</a>
            </div>
        <div class="clearfix"></div>
        </div>
    </footer>
        <?php } } ?>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="/themes/PanicBob/js/vendor/jquery-1.7.2.min.js"><\/script>')</script>
    <script src="/js/jquery-ui-1.8.6.min.js"></script>
    <?php 
    if (isset($quipp->js['footer'])) { 
        foreach($quipp->js['footer'] as $js) {
            echo '<script src="' . $js . '"></script>';
        }
    } 
    
    ?>
    <script src="/themes/Intervue/js/plugins.js"></script>
    <script src="/themes/Intervue/js/main.js"></script>
        <!--tablesorter-->
    <script src="../js/jquery.tablesorter.js"></script>
    <script src="../js/tablesort.js"></script> 
    <script>
    
        <?php if (isset($quipp->js['onload'])) { echo '$(function() { ' . $quipp->js['onload'] . '});'; } ?>
        var _gaq=[['_setAccount','UA-XXXXX-X'],['_trackPageview']];
        (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
        g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
        s.parentNode.insertBefore(g,s)}(document,'script'));
    </script>
</body>
</html>
