<?php
session_start();

// Si ya hay sesión activa, redirigir al dashboard
if (isset($_SESSION['id_usuario'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'conexion.php';

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        $stmt = $conexion->prepare("SELECT id_usuario, nombre, password, rol FROM usuario WHERE email = ? AND estado = 1");
        if ($stmt === false) {
            $error = 'Error del sistema: ' . $conexion->error . '. Verifica que la tabla usuario exista en la BD.';
        } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($password, $usuario['password'])) {
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre']     = $usuario['nombre'];
                $_SESSION['rol']        = $usuario['rol'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'Contraseña incorrecta. Inténtalo de nuevo.';
            }
        } else {
            $error = 'No existe una cuenta con ese correo electrónico.';
        }
        $stmt->close();
        } // fin else stmt
        $conexion->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Producto Terminado</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            position: relative;
            overflow: hidden;
        }

        /* Esferas animadas de fondo */
        body::before, body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            animation: drift 8s ease-in-out infinite alternate;
        }
        body::before {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #6c63ff, #302b63);
            top: -150px; left: -150px;
        }
        body::after {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #ff6584, #24243e);
            bottom: -120px; right: -120px;
            animation-delay: 3s;
        }

        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(40px, 30px) scale(1.08); }
        }

        /* Tarjeta principal */
        .card {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 48px 44px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255,255,255,0.05);
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Ícono / Logo */
        .logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 32px;
        }
        .logo-icon {
            width: 68px; height: 68px;
            background: linear-gradient(135deg, #6c63ff, #a78bfa);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: #fff;
            margin-bottom: 14px;
            box-shadow: 0 8px 24px rgba(108, 99, 255, 0.5);
        }
        .logo h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.5px;
        }
        .logo p {
            font-size: 0.82rem;
            color: rgba(255,255,255,0.5);
            margin-top: 4px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* Mensaje de error */
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.4);
            border-radius: 10px;
            padding: 12px 16px;
            color: #fca5a5;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            animation: shake 0.4s ease;
        }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25%      { transform: translateX(-6px); }
            75%      { transform: translateX(6px); }
        }

        /* Campos del formulario */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 500;
            color: rgba(255,255,255,0.6);
            margin-bottom: 8px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .input-wrapper {
            position: relative;
        }
        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.35);
            font-size: 0.95rem;
            pointer-events: none;
            transition: color 0.3s;
        }
        .input-wrapper input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.3s, background 0.3s, box-shadow 0.3s;
        }
        .input-wrapper input::placeholder { color: rgba(255,255,255,0.25); }
        .input-wrapper input:focus {
            border-color: #6c63ff;
            background: rgba(108, 99, 255, 0.08);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.2);
        }
        .input-wrapper input:focus + i,
        .input-wrapper:focus-within i { color: #a78bfa; }

        /* Toggle contraseña */
        .toggle-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.3);
            cursor: pointer;
            font-size: 0.95rem;
            transition: color 0.3s;
            pointer-events: all;
        }
        .toggle-pass:hover { color: #a78bfa; }

        /* Botón de submit */
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #6c63ff, #a78bfa);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            margin-top: 8px;
            transition: transform 0.2s, box-shadow 0.2s, opacity 0.2s;
            box-shadow: 0 6px 20px rgba(108, 99, 255, 0.45);
            letter-spacing: 0.3px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(108, 99, 255, 0.6);
        }
        .btn-login:active { transform: translateY(0); }

        /* Footer de la tarjeta */
        .card-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 0.8rem;
            color: rgba(255,255,255,0.3);
        }
        .card-footer span { display: block; margin-bottom: 2px; }
    </style>
</head>
<body>
    <div class="card">
        <!-- Logo -->
        <div class="logo">
            <div class="logo-icon">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <h1>Producto Terminado</h1>
            <p>Sistema de Gestión</p>
        </div>

        <!-- Alerta de error -->
        <?php if (!empty($error)): ?>
        <div class="alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <!-- Formulario -->
        <form method="POST" action="index.php" id="loginForm">
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope"></i>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="admin@empresa.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                        autocomplete="email"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    <i class="fa-solid fa-eye toggle-pass" id="togglePass"></i>
                </div>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">
                <i class="fa-solid fa-right-to-bracket" style="margin-right:8px;"></i>
                Iniciar Sesión
            </button>
        </form>

        <div class="card-footer">
            <span>© <?= date('Y') ?> Producto Terminado</span>
            <span>Todos los derechos reservados</span>
        </div>
    </div>

    <script>
        // Toggle visibilidad contraseña
        const toggleBtn = document.getElementById('togglePass');
        const passInput = document.getElementById('password');
        toggleBtn.addEventListener('click', () => {
            const isPass = passInput.type === 'password';
            passInput.type = isPass ? 'text' : 'password';
            toggleBtn.classList.toggle('fa-eye', !isPass);
            toggleBtn.classList.toggle('fa-eye-slash', isPass);
        });

        // Loading state al enviar
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('btnLogin');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right:8px;"></i>Verificando...';
            btn.disabled = true;
        });
    </script>
</body>
</html>
