<?php
// Script para insertar datos de ejemplo
require_once 'db.php';
require_once '../includes/functions.php';

// Plataformas de streaming
$plataformas = [
    [
        'nombre' => 'Netflix',
        'logo' => 'assets/rec/logos/netflix.png',
        'url' => 'https://www.netflix.com'
    ],
    [
        'nombre' => 'Disney+',
        'logo' => 'assets/rec/logos/disney.png',
        'url' => 'https://www.disneyplus.com'
    ],
    [
        'nombre' => 'Amazon Prime',
        'logo' => 'assets/rec/logos/prime.png',
        'url' => 'https://www.primevideo.com'
    ],
    [
        'nombre' => 'HBO Max',
        'logo' => 'assets/rec/logos/hbo.png',
        'url' => 'https://www.hbomax.com'
    ],
    [
        'nombre' => 'Apple TV+',
        'logo' => 'assets/rec/logos/apple.png',
        'url' => 'https://www.apple.com/apple-tv-plus'
    ]
];

// Insertar plataformas
foreach ($plataformas as $plataforma) {
    $sql = "INSERT INTO plataformas (nombre, logo, url) VALUES (?, ?, ?)";
    query($sql, [$plataforma['nombre'], $plataforma['logo'], $plataforma['url']]);
}

// Contenidos de ejemplo
$contenidos = [
    [
        'titulo' => 'Your Friendly Neighborhood Spider-Man',
        'tipo' => 'serie',
        'imagen' => 'assets/rec/spiderman.jpeg',
        'sinopsis' => 'YOUR FRIENDLY NEIGHBORHOOD SPIDER-MAN" ES LA NUEVA SERIE DE MARVEL QUE SE ESTRENÓ EL 29 DE ENERO Y QUE REDEFINE EL HEROÍSMO EN EL CONTEXTO URBANO. LA TRAMA SIGUE A PETER PARKER MIENTRAS SE ENFRENTA, NO SOLO A VILLANOS ICÓNICOS, SINO TAMBIÉN A LOS DILEMAS COTIDIANOS DE LA VIDA EN LA CIUDAD. CON UN TONO FRESCO Y AUDAZ, LA SERIE MUESTRA AL HÉROE LIDIANDO CON LA DUALIDAD DE SU EXISTENCIA: LA LUCHA INTERNA POR MANTENER SU IDENTIDAD SECRETA Y LA RESPONSABILIDAD DE SERVIR A LA COMUNIDAD QUE TANTO AMA. CADA EPISODIO COMBINA ACCIÓN VERTIGINOSA, HUMOR REFRESCANTE Y MOMENTOS DE INTROSPECCIÓN, TODO ENVUELTO EN UNA ESTÉTICA VISUAL QUE RECUERDA A UN CÓMIC CLÁSICO PERO CON TOQUES MODERNOS. EN ESTE UNIVERSO, LA VERDADERA FUERZA DE SPIDER-MAN RADICA EN SU CAPACIDAD PARA CONECTAR CON LA GENTE COMÚN, CONVIRTIÉNDOSE EN UN SÍMBOLO DE ESPERANZA Y HUMANIDAD EN MEDIO DEL CAOS.',
        'fecha_estreno' => '2024-01-29',
        'calificacion' => 10.0,
        'temporadas' => 1,
        'episodios' => 10,
        'generos' => 'ACCIÓN, COMEDIA, DRAMA, SUPER HÉROES, ESCOLAR, FAMILIAR',
        'plataformas' => [1, 2] // Netflix y Disney+
    ],
    [
        'titulo' => 'The Mandalorian',
        'tipo' => 'serie',
        'imagen' => 'assets/rec/mandalorian.jpg',
        'sinopsis' => 'Ambientada después de la caída del Imperio y antes de la aparición de la Primera Orden, la serie sigue las tribulaciones de un pistolero solitario en los confines de la galaxia, lejos de la autoridad de la Nueva República.',
        'fecha_estreno' => '2019-11-12',
        'calificacion' => 9.2,
        'temporadas' => 3,
        'episodios' => 24,
        'generos' => 'ACCIÓN, AVENTURA, CIENCIA FICCIÓN, WESTERN',
        'plataformas' => [2] // Disney+
    ],
    [
        'titulo' => 'Stranger Things',
        'tipo' => 'serie',
        'imagen' => 'assets/rec/stranger.jpg',
        'sinopsis' => 'Cuando un niño desaparece, un pequeño pueblo descubre un misterio que involucra experimentos secretos, fuerzas sobrenaturales aterradoras y una niña muy extraña.',
        'fecha_estreno' => '2016-07-15',
        'calificacion' => 8.7,
        'temporadas' => 4,
        'episodios' => 34,
        'generos' => 'DRAMA, FANTASÍA, HORROR, MISTERIO, CIENCIA FICCIÓN',
        'plataformas' => [1] // Netflix
    ],
    [
        'titulo' => 'Avengers: Endgame',
        'tipo' => 'pelicula',
        'imagen' => 'assets/rec/endgame.jpg',
        'sinopsis' => 'Después de los devastadores eventos de Avengers: Infinity War, el universo está en ruinas. Con la ayuda de los aliados restantes, los Vengadores se reúnen una vez más para revertir las acciones de Thanos y restaurar el equilibrio en el universo.',
        'fecha_estreno' => '2019-04-26',
        'calificacion' => 9.5,
        'temporadas' => NULL,
        'episodios' => NULL,
        'generos' => 'ACCIÓN, AVENTURA, CIENCIA FICCIÓN, FANTASÍA, SUPER HÉROES',
        'plataformas' => [2] // Disney+
    ],
    [
        'titulo' => 'Joker',
        'tipo' => 'pelicula',
        'imagen' => 'assets/rec/joker.jpg',
        'sinopsis' => 'En Gotham City, el comediante con problemas mentales Arthur Fleck es ignorado y maltratado por la sociedad. Luego se embarca en una espiral descendente de revolución y crímenes sangrientos. Este camino lo pone cara a cara con su alter ego: el Joker.',
        'fecha_estreno' => '2019-10-04',
        'calificacion' => 8.4,
        'temporadas' => NULL,
        'episodios' => NULL,
        'generos' => 'CRIMEN, DRAMA, THRILLER, PSICOLÓGICO',
        'plataformas' => [3, 4] // Amazon Prime y HBO Max
    ],
    [
        'titulo' => 'Batman: The Killing Joke',
        'tipo' => 'libro',
        'imagen' => 'assets/rec/killingjoke.jpg',
        'sinopsis' => 'Una de las historias más oscuras del Joker. Según una posible historia de origen, el Joker era un comediante fracasado que se volvió loco después de un mal día y se convirtió en el Príncipe Payaso del Crimen.',
        'fecha_estreno' => '1988-03-29',
        'calificacion' => 9.0,
        'temporadas' => NULL,
        'episodios' => NULL,
        'generos' => 'CÓMIC, SUPER HÉROES, DRAMA, CRIMEN',
        'plataformas' => []
    ]
];

// Insertar contenidos
foreach ($contenidos as $contenido) {
    $sql = "INSERT INTO contenidos (titulo, tipo, imagen, sinopsis, fecha_estreno, calificacion, temporadas, episodios, generos) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    query($sql, [
        $contenido['titulo'], 
        $contenido['tipo'], 
        $contenido['imagen'], 
        $contenido['sinopsis'], 
        $contenido['fecha_estreno'], 
        $contenido['calificacion'], 
        $contenido['temporadas'], 
        $contenido['episodios'], 
        $contenido['generos']
    ]);
    
    $contenido_id = $conexion->insert_id;
    
    // Insertar relaciones con plataformas
    if (!empty($contenido['plataformas'])) {
        $sql = "INSERT INTO contenido_plataforma (contenido_id, plataforma_id) VALUES (?, ?)";
        $stmt = $conexion->prepare($sql);
        
        foreach ($contenido['plataformas'] as $plataforma_id) {
            $stmt->bind_param("ii", $contenido_id, $plataforma_id);
            $stmt->execute();
        }
    }
}

echo "Datos de ejemplo insertados correctamente.";
?>