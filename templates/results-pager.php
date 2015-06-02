<?php

$forrigelink = $_SERVER['REQUEST_URI'] . "&posisjon=" . ($posisjon - $treffperside);
$nestelink   = $_SERVER['REQUEST_URI'] . "&posisjon=" . ($posisjon + $treffperside);

$forrigeposisjon = ($posisjon - $treffperside);
$nesteposisjon   = ($posisjon + $treffperside - 1);
if ($nesteposisjon > $antallfunnet) {
    $nesteposisjon = $antallfunnet;
}

// Hva skal det stå på knappen?
if (($nesteposisjon + $treffperside) > $antallfunnet) {
    $antalligjen = $antallfunnet - $nesteposisjon;
} else {
    $antalligjen = $treffperside;
}

?>
<div class="ils-results-pager">

    <?php if ($antallfunnet >= $treffperside): ?>

        <div class="buttons">
            <?php if ($forrigeposisjon >= 1): ?>
                <button onclick="history.go(-1);">&laquo; Forrige <?= $treffperside ?></button>
            <?php endif; ?>
            <?php if ($nesteposisjon < $antallfunnet): ?>
                <button onclick="showreglitreframeLoading();location.href='<?= $nestelink  ?>'">Neste <?= $antalligjen ?> &raquo;</button>
            <?php endif;  ?>
        </div>

        <p>
            Viser treff <?= $posisjon ?>-<?= $nesteposisjon ?>
            av <?= $antallfunnet ?> ved søk<?= $ibokhylla ?>etter '<?= $qsokeord ?>'
        </p>

    <?php else: ?>

        <p>
            Viser treff 1-<?= $antallfunnet ?>
            ved søk<?= $ibokhylla ?>etter '<?= $qsokeord ?>'
        </p>

    <?php endif; ?>

</div>
