<?php
$viewsDir = __DIR__ . '/resources/views';
$replacements = [
    'Toutes les réservations' => 'Tous les enregistrements',
    'Valider cette réservation' => 'Valider cet enregistrement',
    'Rejeter cette réservation' => 'Rejeter cet enregistrement',
    'Réservation validée' => 'Enregistrement validé',
    'Réservation rejetée' => 'Enregistrement rejeté',
];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir));
foreach ($it as $f) {
    if (!$f->isFile() || $f->getExtension() !== 'php') continue;
    $c = file_get_contents($f->getPathname());
    $o = $c;
    foreach ($replacements as $from => $to) $c = str_replace($from, $to, $c);
    if ($c !== $o) file_put_contents($f->getPathname(), $c);
}
