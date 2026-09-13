<?php if (!empty($appLayout)): ?>
  <footer class="site-footer">&copy; Shameth Fernando</footer>
  </div>
</div>
<?php endif; ?>
<?php
$includeAppJs = $includeAppJs ?? true;
if (!empty($includeAppJs)) {
  $jsFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'app.js';
  if (is_file($jsFile)) {
    echo '<script>' . file_get_contents($jsFile) . '</script>';
  }
}
?>
</body>
</html>
