<?php

/**
 * CotizacionEntity — Solicitud de cotización generada en la tienda.
 *
 * Reemplaza a PedidoEntity en el flujo público: la tienda ya NO
 * genera pedidos ni descuenta stock. Una cotización es una intención
 * de compra que se cierra por WhatsApp y se factura en AnaMarcolPOS.
 */
class CotizacionEntity extends BaseEntity
{
    // ─────────────────────────────────────────────
    // PROPIEDADES — idénticas a columnas de la BD
    // ─────────────────────────────────────────────
    public ?int    $id              = null;
    public ?string $codigo          = null;
    public ?int    $cliente_id      = null;
    public ?string $nombre_cliente  = null;
    public ?string $wa_numero       = null;
    public ?string $tipo_entrega    = 'Retiro';
    public ?string $direccion_envio = null;
    public ?int    $zona_id         = null;
    public ?float  $subtotal        = 0;
    public ?float  $costo_envio     = 0;
    public ?float  $total           = 0;
    public ?string $nota            = null;
    public ?string $estado          = 'Nueva';
    public ?string $created_at      = null;
    public ?string $updated_at      = null;

    // Campos adicionales del JOIN
    public ?string $zona_nombre     = null;
    public ?int    $total_items     = 0;

    // ─────────────────────────────────────────────
    // ESTADOS VÁLIDOS
    // Nueva     → recién llegó de la tienda
    // Atendida  → ya se contestó por WhatsApp
    // Cerrada   → terminó en venta (se factura en el POS)
    // Descartada→ el cliente no siguió
    // ─────────────────────────────────────────────
    public const ESTADOS = ['Nueva', 'Atendida', 'Cerrada', 'Descartada'];

    // ─────────────────────────────────────────────
    // HELPERS DE PRESENTACIÓN
    // ─────────────────────────────────────────────

    public function getCodigoFormateado(): string
    {
        return '#' . ($this->codigo ?? str_pad((string)($this->id ?? 0), 6, '0', STR_PAD_LEFT));
    }

    public function getTotalFormateado(): string
    {
        return 'L. ' . number_format((float)$this->total, 2);
    }

    public function esEnvio(): bool
    {
        return $this->tipo_entrega === 'Envio';
    }

    public function esRetiro(): bool
    {
        return !$this->esEnvio();
    }

    public function getBadgeEstado(): string
    {
        return match ($this->estado) {
            'Nueva'      => 'bg-danger',
            'Atendida'   => 'bg-warning text-dark',
            'Cerrada'    => 'bg-success',
            'Descartada' => 'bg-secondary',
            default      => 'bg-secondary',
        };
    }

    public function getFechaFormateada(): string
    {
        if (!$this->created_at) return '—';
        return date('d/m/Y h:i A', strtotime($this->created_at));
    }

    // ─────────────────────────────────────────────
    // WHATSAPP
    //
    // Dos direcciones distintas y NO intercambiables:
    //   • getWhatsAppUrlEstudio() — la usa la TIENDA: el cliente
    //     envía su cotización al número del estudio (WA_NUMBER).
    //   • getWhatsAppUrlCliente() — la usa el PANEL: la dueña
    //     responde al número que dejó el cliente.
    // ─────────────────────────────────────────────

    // Detalle de la cotización en texto plano — base de ambos mensajes
    private function lineasDetalle(array $detalle): string
    {
        $lineas = '';
        foreach ($detalle as $item) {
            $variante = !empty($item['variante_nombre']) ? " ({$item['variante_nombre']})" : '';
            $lineas  .= "• {$item['nombre_producto']}{$variante} x{$item['cantidad']} — L. "
                      . number_format((float)$item['subtotal'], 2) . "\n";
        }
        return $lineas;
    }

    // Mensaje que el CLIENTE envía al estudio
    public function getMensajeEstudio(array $detalle = []): string
    {
        $msg  = "¡Hola Ana Marcol Makeup Studio! 👋\n";
        $msg .= "Quiero cotizar estos productos:\n\n";
        $msg .= $this->lineasDetalle($detalle);
        $msg .= "\n💰 Subtotal: L. " . number_format((float)$this->subtotal, 2) . "\n";

        if ($this->esEnvio()) {
            $msg .= "🛵 Envío" . ($this->zona_nombre ? " ({$this->zona_nombre})" : '')
                  . ": L. " . number_format((float)$this->costo_envio, 2) . "\n";
            if (!empty($this->direccion_envio)) {
                $msg .= "📍 Dirección: {$this->direccion_envio}\n";
            }
        } else {
            $msg .= "🏪 Entrega: Retiro en el estudio\n";
        }

        $msg .= "🧾 Total estimado: " . $this->getTotalFormateado() . "\n\n";

        if (!empty($this->nota)) {
            $msg .= "📝 Nota: {$this->nota}\n\n";
        }

        $msg .= "👤 {$this->nombre_cliente}\n";
        $msg .= "🔖 Cotización {$this->getCodigoFormateado()}";

        return $msg;
    }

    // Enlace que abre WhatsApp del ESTUDIO con el mensaje precargado
    public function getWhatsAppUrlEstudio(array $detalle = []): string
    {
        return 'https://wa.me/' . WA_NUMBER . '?text=' . urlencode($this->getMensajeEstudio($detalle));
    }

    // Mensaje con el que el ESTUDIO responde al cliente
    public function getMensajeCliente(array $detalle = []): string
    {
        $nombre = $this->nombre_cliente ?: 'Cliente';
        $msg    = "Hola {$nombre} 👋\n";
        $msg   .= "Gracias por tu cotización {$this->getCodigoFormateado()} en Ana Marcol Makeup Studio 💄\n\n";
        $msg   .= $this->lineasDetalle($detalle);
        $msg   .= "\n🧾 Total estimado: " . $this->getTotalFormateado() . "\n\n";
        $msg   .= "¿Confirmamos tu pedido? 😊";
        return $msg;
    }

    // Número del cliente en formato internacional, listo para wa.me
    public function getWaNumeroInternacional(): string
    {
        $numero = preg_replace('/[^0-9]/', '', $this->wa_numero ?? '');
        // Números hondureños de 8 dígitos → anteponer código de país
        return strlen($numero) === 8 ? '504' . $numero : $numero;
    }

    // Enlace que abre WhatsApp del CLIENTE (para responderle desde el panel)
    public function getWhatsAppUrlCliente(array $detalle = []): string
    {
        return 'https://wa.me/' . $this->getWaNumeroInternacional()
             . '?text=' . urlencode($this->getMensajeCliente($detalle));
    }

    // ─────────────────────────────────────────────
    // VALIDACIÓN
    // ─────────────────────────────────────────────
    public function isValid(): bool
    {
        $this->clearErrors();

        if (empty($this->nombre_cliente)) {
            $this->addError('El nombre del cliente es obligatorio.');
        }

        if (!in_array($this->tipo_entrega, ['Retiro', 'Envio'], true)) {
            $this->addError('Tipo de entrega inválido.');
        }

        if ($this->esEnvio() && empty($this->direccion_envio)) {
            $this->addError('La dirección de entrega es obligatoria para envíos.');
        }

        if ((float)$this->total < 0) {
            $this->addError('El total no puede ser negativo.');
        }

        return !$this->hasErrors();
    }
}
