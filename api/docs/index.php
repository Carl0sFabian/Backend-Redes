<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Swagger UI - Edubridge API Documentation</title>
  <!-- Swagger UI Estilos oficiales desde CDN -->
  <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui.css" />
  <link rel="icon" type="image/png" href="https://unpkg.com/swagger-ui-dist@4.15.5/favicon-32x32.png" sizes="32x32" />
  <link rel="icon" type="image/png" href="https://unpkg.com/swagger-ui-dist@4.15.5/favicon-16x16.png" sizes="16x16" />
  <style>
    html {
      box-sizing: border-box;
      overflow: -moz-scrollbars-vertical;
      overflow-y: scroll;
    }
    *, *:before, *:after {
      box-sizing: inherit;
    }
    body {
      margin: 0;
      background: #fafafa;
    }
    /* Estilización del header superior de Swagger */
    .swagger-ui .topbar {
      background-color: #0b0f19;
      border-bottom: 2px solid #3b82f6;
    }
    .swagger-ui .topbar a span {
      color: #f3f4f6;
      font-weight: 700;
    }
  </style>
</head>

<body>
  <!-- Contenedor donde se dibuja la interfaz -->
  <div id="swagger-ui"></div>

  <!-- Scripts oficiales de Swagger UI desde CDN -->
  <script src="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui-bundle.js" charset="UTF-8"> </script>
  <script src="https://unpkg.com/swagger-ui-dist@4.15.5/swagger-ui-standalone-preset.js" charset="UTF-8"> </script>
  <script>
    window.onload = function() {
      // Inicialización de Swagger UI apuntando a openapi.json
      const ui = SwaggerUIBundle({
        url: "openapi.json",
        dom_id: '#swagger-ui',
        deepLinking: true,
        presets: [
          SwaggerUIBundle.presets.apis,
          SwaggerUIStandalonePreset
        ],
        plugins: [
          SwaggerUIBundle.plugins.DownloadUrl
        ],
        layout: "StandaloneLayout"
      });
      window.ui = ui;
    };
  </script>
</body>
</html>
