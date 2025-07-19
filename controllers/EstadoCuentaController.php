<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/EstadoCuenta.php';

class EstadoCuentaController {
    private $db;
    private $conn;
    private $estadoCuenta;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
        $this->estadoCuenta = new EstadoCuenta($this->conn);
        header("Content-Type: application/json");
    }

    public function generarEstadoCuenta() {
        $data = json_decode(file_get_contents("php://input"), true);
        $rol = $data['rol'] ?? null;
        $celulaId = $data['celula_id'] ?? null;
        $fechaInicio = $data['fecha_inicio'] ?? null;
        $fechaFin = $data['fecha_fin'] ?? null;

        if (!$rol || !$fechaInicio || !$fechaFin) {
            http_response_code(400);
            echo json_encode(['message' => 'Rol y fechas son requeridos']);
            return;
        }

        if ($fechaInicio > $fechaFin || $fechaInicio > date('Y-m-d')) {
            http_response_code(400);
            echo json_encode(['message' => 'Rango de fechas inválido']);
            return;
        }

        // Obtener todos los datos necesarios
        $resumen = $this->estadoCuenta->getResumen($rol, $celulaId, $fechaInicio, $fechaFin);
        $lineaTiempo = $this->estadoCuenta->getLineaTiempo($rol, $celulaId, $fechaInicio, $fechaFin);
        $detalle = $this->estadoCuenta->getDetalle($rol, $celulaId, $fechaInicio, $fechaFin);
        $pastel = $this->estadoCuenta->getPastel($rol, $celulaId, $fechaInicio, $fechaFin);
        $barras = $rol === 'P' ? $this->estadoCuenta->getBarrasPorCelula($fechaInicio, $fechaFin) : [];
        $celulaNombre = ($rol === 'P' || $rol === 'TG')
            ? 'IAFCJ Tula de Allende'
            : $this->estadoCuenta->getCelulaNombre($celulaId);

        echo json_encode([
            'resumen' => $resumen,
            'linea_tiempo' => $lineaTiempo,
            'detalle' => $detalle,
            'pastel' => $pastel,
            'barras' => $barras,
            'firmas' => $this->getFirmas($rol),
            'pie_pagina' => $this->getPiePagina($rol, $celulaId),
            'celula_nombre' => $celulaNombre  
        ]);

        $this->db->closeConnection();
    }

    private function getFirmas($rol) {
        if ($rol === 'P' || $rol === 'TG') {
            return ['Administrador General', 'Tesorero General', 'Testigo 1', 'Testigo 2'];
        } elseif ($rol === 'TC') {
            return ['Administrador General', 'Tesorero General', 'Tesorero de Célula', 'Testigo 1', 'Testigo 2'];
        }
        return [];
    }

    private function getPiePagina($rol, $celulaId) {
        if ($rol === 'TC') {
            return "Estado de Cuenta | IAFCJ Tula de Allende Célula ($celulaId) | Página X";
        }
        return "Estado de Cuenta | IAFCJ Tula de Allende | Página X";
    }
}
