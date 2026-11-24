<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$zones = App\Models\ZoneFiscale::withoutGlobalScopes()->get(['id','nom','commune_id','geometrie']);
echo "Total zones_fiscales: " . $zones->count() . "\n";
foreach($zones as $z) {
    $hasGeom = !empty($z->geometrie) ? 'YES' : 'NO';
    echo "  ID={$z->id} Nom={$z->nom} CommuneID={$z->commune_id} HasGeom={$hasGeom}\n";
}

echo "\nPostGIS tables:\n";
try {
    $rows = DB::select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public' ORDER BY tablename");
    foreach($rows as $r) {
        $t = $r->tablename;
        if (str_contains($t, 'commune') || str_contains($t, 'polygone') || str_contains($t, 'sandiara') || str_contains($t, 'zone')) {
            echo "  {$t}\n";
        }
    }
} catch(\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
