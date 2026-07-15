<?php

use App\Config\Config;

$audioBaseUrl = Config::assetsUrl() . '/MP3';
?>
<script>
    window.BibliotecaAudioConfig = Object.freeze({
        audioBaseUrl: <?= json_encode(
            $audioBaseUrl,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
        ) ?>,
    });
</script>
<script src="<?= Config::assetsUrl() ?>/JavaScript/tocaDiscos.js?v=audio-1"></script>
<script src="<?= Config::assetsUrl() ?>/JavaScript/WalkMan.js?v=audio-1"></script>
