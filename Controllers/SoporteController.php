<?php

class SoporteController
{
    private string $apiUrl   = 'http://18.218.192.129/APIR/v1/';
    private string $apiToken = 'ec3c2b75e5037b94deb876fd9c18659f7413da10ed809ab513b6f3175dc4cc7c';

    public function __construct()
    {
        Auth::check();
    }

    public function index(): void
    {
        Auth::require('usuarios.ver');

        $pageTitle = 'Soporte';
        $tickets   = [];
        $error     = null;

        $response = $this->apiGet('tickets');

        if ($response['success']) {
            $tickets = $response['data'] ?? [];
        } else {
            $error = $response['message'] ?? 'No se pudo conectar con el servidor de soporte.';
        }

        require_once VIEWS_PATH . 'Soporte' . DS . 'index.php';
    }

    public function registry(): void
    {
        Auth::require('usuarios.ver');

        $pageTitle = 'Nuevo ticket de soporte';
        $error     = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) ||
                !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                header('Location: ' . APP_URL . 'Soporte/registry');
                exit();
            }

            $titulo      = htmlspecialchars(strip_tags(trim($_POST['titulo']      ?? '')));
            $descripcion = htmlspecialchars(strip_tags(trim($_POST['descripcion'] ?? '')));
            $tipo        = htmlspecialchars(strip_tags(trim($_POST['tipo']        ?? 'consulta')));
            $prioridad   = htmlspecialchars(strip_tags(trim($_POST['prioridad']   ?? 'media')));

            if (empty($titulo) || empty($descripcion)) {
                $error = 'El título y la descripción son obligatorios.';
            } else {
                $response = $this->apiPost('tickets/crear', [
                    'titulo'      => $titulo,
                    'descripcion' => $descripcion,
                    'tipo'        => $tipo,
                    'prioridad'   => $prioridad,
                ]);

                if ($response['success']) {
                    $_SESSION['alert'] = [
                        'icon'  => 'success',
                        'title' => '¡Ticket creado!',
                        'text'  => 'Tu ticket #' . ($response['ticket_id'] ?? '') . ' fue enviado correctamente.',
                    ];
                    header('Location: ' . APP_URL . 'Soporte/index');
                    exit();
                } else {
                    $error = $response['message'] ?? 'Error al crear el ticket.';
                }
            }
        }

        require_once VIEWS_PATH . 'Soporte' . DS . 'Registry.php';
    }

    public function ver(string $id = ''): void
    {
        Auth::require('usuarios.ver');

        if (empty($id) || !is_numeric($id)) {
            header('Location: ' . APP_URL . 'Soporte/index');
            exit();
        }

        $pageTitle = 'Ticket #' . str_pad($id, 4, '0', STR_PAD_LEFT);
        $ticket    = null;
        $error     = null;

        $response = $this->apiGet("tickets/{$id}");

        if ($response['success']) {
            $ticket = $response['data'] ?? null;
        } else {
            $error = $response['message'] ?? 'No se pudo obtener el ticket.';
        }

        require_once VIEWS_PATH . 'Soporte' . DS . 'Ver.php';
    }

    public function comentar(): void
    {
        header('Content-Type: application/json');

        if (!Auth::can('usuarios.ver')) {
            echo json_encode(['success' => false, 'message' => 'Sin permiso.']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit();
        }

        if (!Csrf::validate()) {
            echo json_encode(['success' => false, 'message' => 'Token inválido.']);
            exit();
        }

        $ticketId   = (int)($_POST['ticket_id'] ?? 0);
        $comentario = htmlspecialchars(strip_tags(trim($_POST['comentario'] ?? '')));

        if (!$ticketId || empty($comentario)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
            exit();
        }

        $response = $this->apiPost("tickets/{$ticketId}/comentar", [
            'comentario' => $comentario,
            'autor'      => Auth::get('nombre') ?? 'Ana Marcol',
        ]);

        echo json_encode($response);
        exit();
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────
    private function apiGet(string $endpoint): array
    {
        $ch = curl_init($this->apiUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiToken,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ]);

        $body = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $err];
        }

        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Respuesta inválida del servidor.'];
        }

        return $data;
    }

    private function apiPost(string $endpoint, array $payload): array
    {
        $ch = curl_init($this->apiUrl . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiToken,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ]);

        $body = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'message' => 'Error de conexión: ' . $err];
        }

        $data = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Respuesta inválida del servidor.'];
        }

        return $data;
    }
}