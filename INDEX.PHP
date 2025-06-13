<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema de Consulta</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body {
      background: url('https://images.unsplash.com/photo-1508780709619-79562169bc64?auto=format&fit=crop&w=1950&q=80') no-repeat center center fixed;
      background-size: cover;
      position: relative;
    }
    .overlay {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: -1;
    }
    .card {
      background-color: rgba(255, 255, 255, 0.95);
      border-radius: 15px;
    }
  </style>
</head>
<body>
<div class="overlay"></div>
<?php
session_start();
$usuarios = ['luis' => '1234'];
if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: " . basename(__FILE__));
  exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
  $user = $_POST['usuario'] ?? '';
  $pass = $_POST['clave'] ?? '';
  if (isset($usuarios[$user]) && $usuarios[$user] === $pass) {
    $_SESSION['user'] = $user;
  } else {
    $error = "Usuario o clave incorrectos.";
  }
}
if (!isset($_SESSION['user'])):
?>
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <div class="card animate__animated animate__fadeInDown shadow">
        <div class="card-header bg-dark text-white text-center">
          <h4>Iniciar Sesión</h4>
        </div>
        <div class="card-body">
          <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
          <form method="POST">
            <input type="hidden" name="login" value="1">
            <div class="mb-3">
              <label for="usuario" class="form-label">Usuario:</label>
              <input type="text" class="form-control" id="usuario" name="usuario" required>
            </div>
            <div class="mb-3">
              <label for="clave" class="form-label">Clave:</label>
              <input type="password" class="form-control" id="clave" name="clave" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 rounded-pill">
              <i class="bi bi-box-arrow-in-right"></i> Entrar
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
<?php exit; endif; ?>
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-lg-8 col-xl-6">
      <div class="card animate__animated animate__fadeInUp">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h4 class="mb-0"><i class="bi bi-search"></i> Consulta de Datos</h4>
          <a href="?logout=1" class="btn btn-sm btn-light"><i class="bi bi-box-arrow-left"></i> Cerrar sesión</a>
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label for="tipo" class="form-label">Tipo de consulta:</label>
              <select class="form-select" id="tipo" name="tipo" required>
                <option value="dni">DNI</option>
                <option value="ruc">RUC</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="numero" class="form-label">Número:</label>
              <input type="text" class="form-control" id="numero" name="numero" required>
            </div>
            <button type="submit" class="btn btn-success rounded-pill w-100">
              <i class="bi bi-search"></i> Consultar
            </button>
          </form>
          <div id="spinner" class="text-center my-4 d-none">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
          </div>
          <script>
            document.querySelector("form").addEventListener("submit", function () {
              document.getElementById("spinner").classList.remove("d-none");
            });
          </script>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['numero']) && !empty($_POST['tipo']) && !isset($_POST['login'])) {
  $numero = $_POST['numero'];
  $tipo = $_POST['tipo'];
  $token = 'da305ec699e538ac4b2066d55ba47a20b55ca8fa4ad642e51bb8dbf1df4e2107';

  $url = "https://apiperu.dev/api/{$tipo}";
  $data = ($tipo === 'dni') ? ['dni' => $numero] : ['ruc' => $numero];

  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json',
      'Authorization: Bearer ' . $token
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);

  $obj = json_decode($response, true);

  if (!empty($obj['data'])) {
    echo "<div class='card mt-4 shadow-lg animate__animated animate__fadeInUp'>
            <div class='card-header bg-success text-white rounded-top'>
              <h5 class='mb-0'><i class='bi bi-info-circle-fill'></i> Resultado</h5>
            </div>
            <div class='card-body'>";
    if ($tipo === 'dni') {
      echo "<p><strong>DNI:</strong> {$obj['data']['numero']}</p>
            <p><strong>Nombre completo:</strong> {$obj['data']['nombre_completo']}</p>
            <p><strong>Nombres:</strong> {$obj['data']['nombres']}</p>
            <p><strong>Apellido paterno:</strong> {$obj['data']['apellido_paterno']}</p>
            <p><strong>Apellido materno:</strong> {$obj['data']['apellido_materno']}</p>";
    } else if ($tipo === 'ruc') {
      echo "<p><strong>RUC:</strong> {$obj['data']['ruc']}</p>
            <p><strong>Estado:</strong> {$obj['data']['estado']}</p>
            <p><strong>Condición:</strong> {$obj['data']['condicion']}</p>
            <p><strong>Dirección:</strong> {$obj['data']['direccion']}</p>
            <p><strong>Departamento:</strong> {$obj['data']['departamento']}</p>
            <p><strong>Provincia:</strong> {$obj['data']['provincia']}</p>
            <p><strong>Distrito:</strong> {$obj['data']['distrito']}</p>";
    }
    echo "</div></div>";
  } else {
    echo "<div class='alert alert-warning mt-4'>No se encontraron datos para el número ingresado.</div>";
  }
}
?>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
